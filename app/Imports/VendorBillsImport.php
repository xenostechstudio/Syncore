<?php

namespace App\Imports;

use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VendorBillsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $billNumber = trim($row['bill_number'] ?? '');

                // Find supplier by name
                $supplierId = null;
                if (!empty($row['supplier'])) {
                    $supplier = Supplier::where('name', 'ilike', trim($row['supplier']))->first();
                    $supplierId = $supplier?->id;
                }

                if (empty($supplierId)) {
                    $this->errors[] = "Row " . ($index + 2) . ": Supplier not found";
                    continue;
                }

                $vendorBill = !empty($billNumber) ? VendorBill::where('bill_number', $billNumber)->first() : null;

                $data = [
                    'supplier_id' => $supplierId,
                    'vendor_reference' => $row['vendor_reference'] ?? null,
                    'bill_date' => !empty($row['bill_date']) ? $row['bill_date'] : now(),
                    'due_date' => !empty($row['due_date']) ? $row['due_date'] : now()->addDays(30),
                    'status' => $row['status'] ?? 'draft',
                    'subtotal' => (float) ($row['subtotal'] ?? 0),
                    'tax' => (float) ($row['tax'] ?? 0),
                    'total' => (float) ($row['total'] ?? 0),
                    'notes' => $row['notes'] ?? null,
                ];

                if ($vendorBill) {
                    $vendorBill->update($data);
                    $this->updated++;
                } else {
                    if (!empty($billNumber)) {
                        $data['bill_number'] = $billNumber;
                    }
                    VendorBill::create($data);
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
            'supplier' => 'required|string|max:255',
            'total' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,pending,paid,cancelled',
        ];
    }
}
