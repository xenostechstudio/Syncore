<?php

namespace App\Listeners\Purchase;

use App\Events\LowStockDetected;
use App\Events\PurchaseOrderReceived;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Warehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdateInventoryOnPurchaseReceived implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PurchaseOrderReceived $event): void
    {
        $purchaseOrder = $event->purchaseOrder;

        // Get default receiving warehouse (first warehouse or create one)
        $warehouse = Warehouse::where('is_default', true)->first() 
            ?? Warehouse::first();

        if (!$warehouse) {
            return;
        }

        DB::transaction(function () use ($purchaseOrder, $warehouse) {
            foreach ($purchaseOrder->items as $item) {
                if (!$item->product_id) {
                    continue;
                }

                // Increase stock
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouse->id,
                    ],
                    ['quantity' => 0]
                );

                $stock->increment('quantity', $item->quantity);

                // Update product total quantity
                $totalStock = InventoryStock::where('product_id', $item->product_id)->sum('quantity');
                $item->product?->update(['quantity' => $totalStock]);
            }
        });
    }
}
