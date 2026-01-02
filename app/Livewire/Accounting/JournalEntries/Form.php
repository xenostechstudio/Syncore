<?php

namespace App\Livewire\Accounting\JournalEntries;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalLine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Journal Entry')]
class Form extends Component
{
    public ?int $entryId = null;
    public ?JournalEntry $entry = null;

    public string $entryDate = '';
    public string $reference = '';
    public string $description = '';
    public array $lines = [];

    protected function rules(): array
    {
        return [
            'entryDate' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->entryId = $id;

        if ($id) {
            $this->entry = JournalEntry::with('lines')->findOrFail($id);
            $this->entryDate = $this->entry->entry_date->format('Y-m-d');
            $this->reference = $this->entry->reference ?? '';
            $this->description = $this->entry->description ?? '';
            $this->lines = $this->entry->lines->map(fn ($line) => [
                'id' => $line->id,
                'account_id' => $line->account_id,
                'description' => $line->description ?? '',
                'debit' => $line->debit,
                'credit' => $line->credit,
            ])->toArray();
        } else {
            $this->entryDate = now()->format('Y-m-d');
            $this->addLine();
            $this->addLine();
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'id' => null,
            'account_id' => '',
            'description' => '',
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 2) {
            unset($this->lines[$index]);
            $this->lines = array_values($this->lines);
        }
    }

    public function getTotalDebit(): float
    {
        return collect($this->lines)->sum('debit');
    }

    public function getTotalCredit(): float
    {
        return collect($this->lines)->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->getTotalDebit() - $this->getTotalCredit()) < 0.01;
    }

    public function save(): void
    {
        $this->validate();

        if (!$this->isBalanced()) {
            session()->flash('error', 'Journal entry must be balanced (total debits = total credits).');
            return;
        }

        $data = [
            'entry_date' => $this->entryDate,
            'reference' => $this->reference ?: null,
            'reference_type' => 'manual',
            'description' => $this->description ?: null,
            'total_debit' => $this->getTotalDebit(),
            'total_credit' => $this->getTotalCredit(),
            'created_by' => auth()->id(),
        ];

        if ($this->entryId) {
            $this->entry->update($data);
            $entry = $this->entry;

            // Delete removed lines
            $existingIds = collect($this->lines)->pluck('id')->filter()->toArray();
            $entry->lines()->whereNotIn('id', $existingIds)->delete();
        } else {
            $data['entry_number'] = JournalEntry::generateNumber();
            $data['status'] = 'draft';
            $entry = JournalEntry::create($data);
        }

        // Save lines
        foreach ($this->lines as $line) {
            if (!empty($line['account_id']) && ($line['debit'] > 0 || $line['credit'] > 0)) {
                $lineData = [
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?: null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ];

                if (!empty($line['id'])) {
                    JournalLine::find($line['id'])?->update($lineData);
                } else {
                    JournalLine::create($lineData);
                }
            }
        }

        $entry->recalculateTotals();

        session()->flash('success', $this->entryId ? 'Journal entry updated.' : 'Journal entry created.');
        $this->redirect(route('accounting.journal-entries.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.accounting.journal-entries.form', [
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(),
            'totalDebit' => $this->getTotalDebit(),
            'totalCredit' => $this->getTotalCredit(),
            'balanced' => $this->isBalanced(),
        ]);
    }
}
