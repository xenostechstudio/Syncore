<?php

namespace App\Imports;

use App\Imports\Concerns\HasImportTracking;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SalesOrdersImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $orderNumber = trim($row['order_number'] ?? '');

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

                $salesOrder = !empty($orderNumber) ? SalesOrder::where('order_number', $orderNumber)->first() : null;

                $data = [
                    'customer_id' => $customerId,
                    'order_date' => !empty($row['order_date']) ? $row['order_date'] : now(),
                    'expected_delivery_date' => !empty($row['expected_delivery_date']) ? $row['expected_delivery_date'] : null,
                    'status' => $row['status'] ?? 'quotation',
                    'payment_terms' => $row['payment_terms'] ?? null,
                    'subtotal' => (float) ($row['subtotal'] ?? 0),
                    'tax' => (float) ($row['tax'] ?? 0),
                    'discount' => (float) ($row['discount'] ?? 0),
                    'total' => (float) ($row['total'] ?? 0),
                    'notes' => $row['notes'] ?? null,
                    'terms' => $row['terms'] ?? null,
                    'shipping_address' => $row['shipping_address'] ?? null,
                ];

                if ($salesOrder) {
                    $salesOrder->update($data);
                    $this->updated++;
                } else {
                    if (!empty($orderNumber)) {
                        $data['order_number'] = $orderNumber;
                    }
                    SalesOrder::create($data);
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
            'status' => 'nullable|in:quotation,sales_order,done,cancelled',
        ];
    }
}

    public function chunkSize(): int
    {
        return 100;
    }
