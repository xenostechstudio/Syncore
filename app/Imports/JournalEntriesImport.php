<?php

namespace App\Imports;

use App\Imports\Concerns\HasImportTracking;
use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class JournalEntriesImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        // Group rows by entry number for multi-line entries
        $grouped = $rows->groupBy(fn($row) => $this->getString($row['entry_number']) ?? 'new_' . uniqid());

        foreach ($grouped as $entryNumber => $lines) {
            try {
                DB::transaction(function () use ($entryNumber, $lines) {
                    $firstRow = $lines->first();

                    // Validate that debits equal credits
                    $totalDebit = $lines->sum(fn($row) => $this->parseNumber($row['debit']));
                    $totalCredit = $lines->sum(fn($row) => $this->parseNumber($row['credit']));

                    if (abs($totalDebit - $totalCredit) > 0.01) {
                        $this->addError(0, "Entry {$entryNumber}: Debits ({$totalDebit}) must equal credits ({$totalCredit})");
                        $this->skipped += $lines->count();
                        return;
                    }

                    // Check if entry exists
                    $entry = str_starts_with($entryNumber, 'new_')
                        ? null
                        : JournalEntry::where('entry_number', $entryNumber)->first();

                    if (!$entry) {
                        $entry = JournalEntry::create([
                            'entry_number' => JournalEntry::generateNumber(),
                            'entry_date' => $this->parseDate($firstRow['date']) ?? now(),
                            'reference' => $this->getString($firstRow['reference']),
                            'description' => $this->getString($firstRow['description']),
                            'status' => 'draft',
                            'total_debit' => $totalDebit,
                            'total_credit' => $totalCredit,
                            'created_by' => auth()->id(),
                        ]);
                        $this->imported++;
                    } else {
                        // Update existing entry
                        $entry->update([
                            'entry_date' => $this->parseDate($firstRow['date']) ?? $entry->entry_date,
                            'reference' => $this->getString($firstRow['reference']) ?? $entry->reference,
                            'description' => $this->getString($firstRow['description']) ?? $entry->description,
                            'total_debit' => $totalDebit,
                            'total_credit' => $totalCredit,
                        ]);
                        // Clear existing lines for re-import
                        $entry->lines()->delete();
                        $this->updated++;
                    }

                    // Add lines
                    foreach ($lines as $index => $row) {
                        $account = Account::where('code', $this->getString($row['account_code']))
                            ->orWhere('name', 'ilike', $this->getString($row['account']))
                            ->first();

                        if (!$account) {
                            $this->addError($index, "Account not found: " . ($row['account_code'] ?? $row['account']));
                            continue;
                        }

                        JournalLine::create([
                            'journal_entry_id' => $entry->id,
                            'account_id' => $account->id,
                            'description' => $this->getString($row['line_description']),
                            'debit' => $this->parseNumber($row['debit']),
                            'credit' => $this->parseNumber($row['credit']),
                        ]);
                    }

                    // Auto-post if requested
                    if (strtolower($this->getString($firstRow['auto_post']) ?? '') === 'yes') {
                        $entry->post();
                    }
                });
            } catch (\Exception $e) {
                $this->addError(0, "Entry {$entryNumber}: " . $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'account_code' => 'required_without:account|string|max:50',
            'account' => 'required_without:account_code|string|max:255',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
