<?php

namespace App\Imports;

use App\Enums\DeliveryOrderState;
use App\Imports\Concerns\HasImportTracking;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DeliveryOrdersImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                DB::transaction(function () use ($row, $index) {
                    $deliveryNumber = $this->getString($row['delivery_number']);

                    // Find sales order if provided
                    $salesOrderId = null;
                    if (!empty($row['sales_order'])) {
                        $salesOrder = SalesOrder::where('order_number', $this->getString($row['sales_order']))->first();
                        if (!$salesOrder) {
                            $this->addError($index, "Sales order not found: " . $row['sales_order']);
                            $this->skipped++;
                            return;
                        }
                        $salesOrderId = $salesOrder->id;
                    }

                    // Find warehouse if provided
                    $warehouseId = null;
                    if (!empty($row['warehouse'])) {
                        $warehouse = Warehouse::where('name', 'ilike', $this->getString($row['warehouse']))->first();
                        $warehouseId = $warehouse?->id;
                    }

                    // Check if delivery order exists
                    $deliveryOrder = $deliveryNumber 
                        ? DeliveryOrder::where('delivery_number', $deliveryNumber)->first() 
                        : null;

                    $status = $this->getString($row['status']) ?? 'pending';
                    $validStatuses = array_column(DeliveryOrderState::cases(), 'value');
                    if (!in_array($status, $validStatuses)) {
                        $status = 'pending';
                    }

                    $data = [
                        'sales_order_id' => $salesOrderId,
                        'warehouse_id' => $warehouseId,
                        'user_id' => auth()->id(),
                        'delivery_date' => $this->parseDate($row['delivery_date']) ?? now(),
                        'actual_delivery_date' => $this->parseDate($row['actual_delivery_date']),
                        'status' => $status,
                        'shipping_address' => $this->getString($row['shipping_address']),
                        'recipient_name' => $this->getString($row['recipient_name']),
                        'recipient_phone' => $this->getString($row['recipient_phone']),
                        'tracking_number' => $this->getString($row['tracking_number']),
                        'courier' => $this->getString($row['courier']),
                        'notes' => $this->getString($row['notes']),
                    ];

                    if ($deliveryOrder) {
                        $deliveryOrder->update($data);
                        $this->updated++;
                    } else {
                        DeliveryOrder::create($data);
                        $this->imported++;
                    }
                });
            } catch (\Exception $e) {
                $this->addError($index, $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'recipient_name' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
