<?php

namespace App\Listeners\Inventory;

use App\Events\DeliveryCompleted;
use App\Events\LowStockDetected;
use App\Models\Inventory\InventoryStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdateStockOnDelivery implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(DeliveryCompleted $event): void
    {
        $deliveryOrder = $event->deliveryOrder;

        DB::transaction(function () use ($deliveryOrder) {
            foreach ($deliveryOrder->items as $item) {
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $deliveryOrder->warehouse_id,
                    ],
                    ['quantity' => 0]
                );

                // Decrease stock
                $stock->decrement('quantity', $item->quantity);

                // Check for low stock
                $product = $item->product;
                if ($product->reorder_level && $stock->quantity <= $product->reorder_level) {
                    LowStockDetected::dispatch(
                        $product,
                        $deliveryOrder->warehouse,
                        $stock->quantity,
                        $product->reorder_level
                    );
                }
            }
        });
    }
}
