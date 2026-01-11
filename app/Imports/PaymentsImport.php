<?php

namespace App\Imports;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PaymentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $paymentNumber = trim($row['payment_number'] ?? '');
                $invoiceNumber = trim($row['invoice_number'] ?? '');

                // Find invoice by number
                $invoiceId = null;
                if (!empty($invoiceNumber)) {
                    $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();
                    $invoiceId = $invoice?->id;
                }

                if (empty($invoiceId)) {
                    $this->errors[] = "Row " . ($index + 2) . ": Invoice not found";
                    continue;
                }

                $payment = !empty($paymentNumber) ? Payment::where('payment_number', $paymentNumber)->first() : null;

                $data = [
                    'invoice_id' => $invoiceId,
                    'payment_date' => !empty($row['payment_date']) ? $row['payment_date'] : now(),
                    'amount' => (float) ($row['amount'] ?? 0),
                    'payment_method' => $row['payment_method'] ?? 'bank_transfer',
                    'reference' => $row['reference'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'status' => $row['status'] ?? 'completed',
                ];

                if ($payment) {
                    $payment->update($data);
                    $this->updated++;
                } else {
                    if (!empty($paymentNumber)) {
                        $data['payment_number'] = $paymentNumber;
                    }
                    Payment::create($data);
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
            'invoice_number' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,e_wallet,other',
        ];
    }
}
