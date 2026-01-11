<?php

namespace App\Listeners\Notification;

use App\Events\LowStockDetected;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLowStockAlert implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        NotificationService::create(
            type: 'low_stock',
            title: 'Low Stock Alert',
            message: "Product '{$event->product->name}' has low stock ({$event->currentStock}) in warehouse '{$event->warehouse->name}'. Reorder level: {$event->reorderLevel}",
            notifiable: $event->product,
            data: [
                'product_id' => $event->product->id,
                'warehouse_id' => $event->warehouse->id,
                'current_stock' => $event->currentStock,
                'reorder_level' => $event->reorderLevel,
            ]
        );
    }
}
