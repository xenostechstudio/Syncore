<?php

namespace App\Imports;

use App\Enums\AdjustmentState;
use App\Imports\Concerns\HasImportTracking;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryAdjustmentItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InventoryAdjustmentsImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                DB::transaction(function () use ($row, $index) {
                    // Find warehouse
                    $warehouse = Warehouse::where('name', 'ilike', $this->getString($row['warehouse']))->first();
                    if (!$warehouse) {
                        $this->addError($index, "Warehouse not found: " . $row['warehouse']);
                        $this->skipped++;
                        return;
                    }

                    // Find product
                    $product = Product::where('sku', $this->getString($row['sku']))
                        ->orWhere('name', 'ilike', $this->getString($row['product']))
                        ->first();
                    
                    if (!$product) {
                        $this->addError($index, "Product not found: " . ($row['sku'] ?? $row['product']));
                        $this->skipped++;
                        return;
                    }

                    $adjustmentType = strtolower($this->getString($row['type']) ?? 'count');
                    if (!in_array($adjustmentType, ['increase', 'decrease', 'count'])) {
                        $adjustmentType = 'count';
                    }

                    // Create adjustment
                    $adjustment = InventoryAdjustment::create([
                        'warehouse_id' => $warehouse->id,
                        'user_id' => auth()->id(),
                        'adjustment_date' => $this->parseDate($row['date']) ?? now(),
                        'adjustment_type' => $adjustmentType,
                        'status' => AdjustmentState::DRAFT->value,
                        'reason' => $this->getString($row['reason']),
                    ]);

                    // Create adjustment item
                    InventoryAdjustmentItem::create([
                        'inventory_adjustment_id' => $adjustment->id,
                        'product_id' => $product->id,
                        'counted_quantity' => (int) $this->parseNumber($row['quantity']),
                    ]);

                    // Auto-post if requested
                    if (strtolower($this->getString($row['auto_post']) ?? '') === 'yes') {
                        $adjustment->post();
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
            'warehouse' => 'required|string|max:255',
            'product' => 'required_without:sku|string|max:255',
            'sku' => 'required_without:product|string|max:100',
            'quantity' => 'required|numeric',
            'type' => 'nullable|in:increase,decrease,count',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
