<?php

namespace App\Imports;

use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PurchaseRfqsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $reference = trim($row['reference'] ?? '');

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

                $purchaseRfq = !empty($reference) ? PurchaseRfq::where('reference', $reference)->first() : null;

                $data = [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $row['supplier'] ?? null,
                    'order_date' => !empty($row['order_date']) ? $row['order_date'] : now(),
                    'expected_arrival' => !empty($row['expected_arrival']) ? $row['expected_arrival'] : null,
                    'status' => $row['status'] ?? 'rfq',
                    'subtotal' => (float) ($row['subtotal'] ?? 0),
                    'tax' => (float) ($row['tax'] ?? 0),
                    'total' => (float) ($row['total'] ?? 0),
                    'notes' => $row['notes'] ?? null,
                ];

                if ($purchaseRfq) {
                    $purchaseRfq->update($data);
                    $this->updated++;
                } else {
                    if (!empty($reference)) {
                        $data['reference'] = $reference;
                    }
                    PurchaseRfq::create($data);
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
            'status' => 'nullable|in:rfq,purchase_order,done,cancelled',
        ];
    }
}
