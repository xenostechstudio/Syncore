<?php

namespace App\Imports;

use App\Imports\Concerns\HasImportTracking;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InvoicesImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $invoiceNumber = trim($row['invoice_number'] ?? '');

                // Find customer by name
                $customerId = null;
                if (!empty($row['customer'])) {
                    $customer = Customer::where('name', 'ilike', trim($row['customer']))->first();
                    $customerId = $customer?->id;
                }

                if (empty($customerId)) {
                    $this->errors[] = "Row " . ($index + 2) . ": Customer not found";
                    continue;
                }

                $invoice = !empty($invoiceNumber) ? Invoice::where('invoice_number', $invoiceNumber)->first() : null;

                $data = [
                    'customer_id' => $customerId,
                    'invoice_date' => !empty($row['invoice_date']) ? $row['invoice_date'] : now(),
                    'due_date' => !empty($row['due_date']) ? $row['due_date'] : now()->addDays(30),
                    'status' => $row['status'] ?? 'draft',
                    'subtotal' => (float) ($row['subtotal'] ?? 0),
                    'tax' => (float) ($row['tax'] ?? 0),
                    'discount' => (float) ($row['discount'] ?? 0),
                    'total' => (float) ($row['total'] ?? 0),
                    'notes' => $row['notes'] ?? null,
                    'terms' => $row['terms'] ?? null,
                ];

                if ($invoice) {
                    $invoice->update($data);
                    $this->updated++;
                } else {
                    if (!empty($invoiceNumber)) {
                        $data['invoice_number'] = $invoiceNumber;
                    }
                    Invoice::create($data);
                    $this->imported++;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'customer' => 'required|string|max:255',
            'total' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,sent,paid,cancelled,overdue',
        ];
    }
}

    public function chunkSize(): int
    {
        return 100;
    }
