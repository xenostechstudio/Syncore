<?php

namespace App\Services;

use App\Enums\AdjustmentState;
use App\Enums\TransferState;
use App\Events\LowStockDetected;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryAdjustmentItem;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\InventoryTransferItem;
use App\Models\Inventory\Product;
use Illuminate\Support\Facades\DB;

/**
 * Inventory Service
 * 
 * Centralized business logic for inventory operations.
 */
class InventoryService
{
    /**
     * Get stock level for a product in a warehouse.
     */
    public function getStock(int $productId, int $warehouseId): int
    {
        return InventoryStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    /**
     * Get total stock for a product across all warehouses.
     */
    public function getTotalStock(int $productId): int
    {
        return InventoryStock::where('product_id', $productId)->sum('quantity');
    }

    /**
     * Adjust stock (increase or decrease).
     */
    public function adjustStock(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $type = 'increase',
        ?string $reason = null
    ): InventoryAdjustment {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $type, $reason) {
            $adjustment = InventoryAdjustment::create([
                'adjustment_number' => InventoryAdjustment::generateAdjustmentNumber($type),
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'adjustment_date' => now(),
                'adjustment_type' => $type,
                'status' => AdjustmentState::DRAFT->value,
                'reason' => $reason,
            ]);

            InventoryAdjustmentItem::create([
                'inventory_adjustment_id' => $adjustment->id,
                'product_id' => $productId,
                'counted_quantity' => $quantity,
            ]);

            // Auto-post the adjustment
            $adjustment->post();

            return $adjustment->fresh(['items']);
        });
    }

    /**
     * Create a stock transfer between warehouses.
     */
    public function createTransfer(
        int $sourceWarehouseId,
        int $destinationWarehouseId,
        array $items,
        ?string $notes = null
    ): InventoryTransfer {
        return DB::transaction(function () use ($sourceWarehouseId, $destinationWarehouseId, $items, $notes) {
            $transfer = InventoryTransfer::create([
                'transfer_number' => InventoryTransfer::generateTransferNumber(),
                'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destinationWarehouseId,
                'user_id' => auth()->id(),
                'transfer_date' => now(),
                'status' => TransferState::DRAFT->value,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $transfer->fresh(['items', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }

    /**
     * Process a transfer (move stock between warehouses).
     */
    public function processTransfer(InventoryTransfer $transfer): bool
    {
        if ($transfer->state->isTerminal()) {
            return false;
        }

        return DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Decrease from source
                $sourceStock = InventoryStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $transfer->source_warehouse_id],
                    ['quantity' => 0]
                );

                if ($sourceStock->quantity < $item->quantity) {
                    throw new \RuntimeException("Insufficient stock for product ID {$item->product_id}");
                }

                $sourceStock->decrement('quantity', $item->quantity);

                // Increase at destination
                $destStock = InventoryStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $transfer->destination_warehouse_id],
                    ['quantity' => 0]
                );
                $destStock->increment('quantity', $item->quantity);

                // Check for low stock
                $this->checkLowStock($item->product_id, $transfer->source_warehouse_id);
            }

            $oldStatus = $transfer->status;
            $transfer->update(['status' => TransferState::COMPLETED->value]);
            $transfer->logStatusChange($oldStatus, $transfer->status, 'Transfer completed');

            return true;
        });
    }

    /**
     * Cancel a transfer.
     */
    public function cancelTransfer(InventoryTransfer $transfer, ?string $reason = null): bool
    {
        if (!$transfer->state->canCancel()) {
            return false;
        }

        $oldStatus = $transfer->status;
        $transfer->update(['status' => TransferState::CANCELLED->value]);
        $transfer->logStatusChange($oldStatus, $transfer->status, $reason ?? 'Cancelled');

        return true;
    }

    /**
     * Check if product has low stock and dispatch event if needed.
     */
    public function checkLowStock(int $productId, int $warehouseId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->reorder_level) {
            return;
        }

        $stock = $this->getStock($productId, $warehouseId);
        
        if ($stock <= $product->reorder_level) {
            $warehouse = \App\Models\Inventory\Warehouse::find($warehouseId);
            if ($warehouse) {
                LowStockDetected::dispatch($product, $warehouse, $stock, $product->reorder_level);
            }
        }
    }

    /**
     * Get products with low stock.
     */
    public function getLowStockProducts(?int $warehouseId = null): \Illuminate\Support\Collection
    {
        $query = InventoryStock::with(['product', 'warehouse'])
            ->whereHas('product', function ($q) {
                $q->whereNotNull('reorder_level')->where('reorder_level', '>', 0);
            });

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->filter(function ($stock) {
            return $stock->quantity <= $stock->product->reorder_level;
        });
    }

    /**
     * Recalculate product total quantity from all warehouses.
     */
    public function recalculateProductQuantity(int $productId): void
    {
        $total = InventoryStock::where('product_id', $productId)->sum('quantity');
        Product::where('id', $productId)->update(['quantity' => $total]);
    }
}
