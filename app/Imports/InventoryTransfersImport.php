<?php

namespace App\Imports;

use App\Enums\TransferState;
use App\Imports\Concerns\HasImportTracking;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\InventoryTransferItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InventoryTransfersImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        // Group rows by transfer number for multi-item transfers
        $grouped = $rows->groupBy(fn($row) => $this->getString($row['transfer_number']) ?? 'new_' . uniqid());

        foreach ($grouped as $transferNumber => $items) {
            try {
                DB::transaction(function () use ($transferNumber, $items) {
                    $firstRow = $items->first();
                    
                    // Find warehouses
                    $sourceWarehouse = Warehouse::where('name', 'ilike', $this->getString($firstRow['source_warehouse']))->first();
                    $destWarehouse = Warehouse::where('name', 'ilike', $this->getString($firstRow['destination_warehouse']))->first();

                    if (!$sourceWarehouse) {
                        $this->addError(0, "Source warehouse not found: " . $firstRow['source_warehouse']);
                        $this->skipped += $items->count();
                        return;
                    }

                    if (!$destWarehouse) {
                        $this->addError(0, "Destination warehouse not found: " . $firstRow['destination_warehouse']);
                        $this->skipped += $items->count();
                        return;
                    }

                    // Check if transfer exists
                    $transfer = str_starts_with($transferNumber, 'new_') 
                        ? null 
                        : InventoryTransfer::where('transfer_number', $transferNumber)->first();

                    if (!$transfer) {
                        $transfer = InventoryTransfer::create([
                            'source_warehouse_id' => $sourceWarehouse->id,
                            'destination_warehouse_id' => $destWarehouse->id,
                            'user_id' => auth()->id(),
                            'transfer_date' => $this->parseDate($firstRow['date']) ?? now(),
                            'expected_arrival_date' => $this->parseDate($firstRow['expected_arrival']),
                            'status' => TransferState::DRAFT->value,
                            'notes' => $this->getString($firstRow['notes']),
                        ]);
                        $this->imported++;
                    } else {
                        $this->updated++;
                    }

                    // Add items
                    foreach ($items as $index => $row) {
                        $product = Product::where('sku', $this->getString($row['sku']))
                            ->orWhere('name', 'ilike', $this->getString($row['product']))
                            ->first();

                        if (!$product) {
                            $this->addError($index, "Product not found: " . ($row['sku'] ?? $row['product']));
                            continue;
                        }

                        InventoryTransferItem::updateOrCreate(
                            [
                                'inventory_transfer_id' => $transfer->id,
                                'product_id' => $product->id,
                            ],
                            [
                                'quantity' => (int) $this->parseNumber($row['quantity']),
                            ]
                        );
                    }
                });
            } catch (\Exception $e) {
                $this->addError(0, "Transfer {$transferNumber}: " . $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'source_warehouse' => 'required|string|max:255',
            'destination_warehouse' => 'required|string|max:255',
            'product' => 'required_without:sku|string|max:255',
            'sku' => 'required_without:product|string|max:100',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
