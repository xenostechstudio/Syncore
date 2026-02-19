<?php

namespace App\Imports;

use App\Events\VendorBillPaid;
use App\Imports\Concerns\HasImportTracking;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VendorBillPaymentsImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                DB::transaction(function () use ($row, $index) {
                    // Find vendor bill
                    $bill = VendorBill::where('bill_number', $this->getString($row['bill_number']))->first();
                    
                    if (!$bill) {
                        $this->addError($index, "Vendor bill not found: " . $row['bill_number']);
                        $this->skipped++;
                        return;
                    }

                    // Check if payment can be registered
                    if (!$bill->state->canRegisterPayment()) {
                        $this->addError($index, "Cannot register payment for bill: " . $row['bill_number']);
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
                        $existingPayment = VendorBillPayment::where('vendor_bill_id', $bill->id)
                            ->where('reference', $reference)
                            ->first();
                        
                        if ($existingPayment) {
                            $this->addError($index, "Payment with reference already exists: " . $reference);
                            $this->skipped++;
                            return;
                        }
                    }

                    // Create payment
                    $payment = VendorBillPayment::create([
                        'vendor_bill_id' => $bill->id,
                        'amount' => $amount,
                        'payment_date' => $this->parseDate($row['payment_date']) ?? now(),
                        'payment_method' => $this->getString($row['payment_method']) ?? 'bank_transfer',
                        'reference' => $reference,
                        'notes' => $this->getString($row['notes']),
                    ]);

                    // Update bill paid amount
                    $newPaidAmount = $bill->paid_amount + $amount;
                    $updateData = ['paid_amount' => $newPaidAmount];

                    if ($newPaidAmount >= $bill->total) {
                        $updateData['status'] = 'paid';
                        $updateData['paid_date'] = now();
                    } elseif ($newPaidAmount > 0) {
                        $updateData['status'] = 'partial';
                    }

                    $bill->update($updateData);

                    // Dispatch event if fully paid
                    if ($bill->status === 'paid') {
                        VendorBillPaid::dispatch($bill, $payment);
                    }

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
            'bill_number' => 'required|string|max:255',
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
