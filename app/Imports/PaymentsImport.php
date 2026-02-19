<?php

namespace App\Imports;

use App\Imports\Concerns\HasImportTracking;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Services\InvoiceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PaymentsImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    protected InvoiceService $invoiceService;

    public function __construct()
    {
        $this->invoiceService = app(InvoiceService::class);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                DB::transaction(function () use ($row, $index) {
                    // Find invoice
                    $invoice = Invoice::where('invoice_number', $this->getString($row['invoice_number']))->first();
                    
                    if (!$invoice) {
                        $this->addError($index, "Invoice not found: " . $row['invoice_number']);
                        $this->skipped++;
                        return;
                    }

                    // Check if payment can be registered
                    if (!$invoice->state->canRegisterPayment()) {
                        $this->addError($index, "Cannot register payment for invoice: " . $row['invoice_number']);
                        $this->skipped++;
                        return;
                    }

                    $amount = $this->parseNumber($row['amount']);
                    if ($amount <= 0) {
                        $this->addError($index, "Invalid payment amount");
                        $this->skipped++;
                        return;
                    }

                    // Check for duplicate payment by reference
                    $reference = $this->getString($row['reference']);
                    if ($reference) {
                        $existingPayment = Payment::where('invoice_id', $invoice->id)
                            ->where('reference', $reference)
                            ->first();
                        
                        if ($existingPayment) {
                            $this->addError($index, "Payment with reference already exists: " . $reference);
                            $this->skipped++;
                            return;
                        }
                    }

                    // Register payment using service
                    $this->invoiceService->registerPayment(
                        $invoice,
                        $amount,
                        $this->getString($row['payment_method']) ?? 'manual',
                        $reference,
                        $this->getString($row['notes'])
                    );

                    $this->imported++;
                });
            } catch (\Exception $e) {
                $this->addError($index, $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
