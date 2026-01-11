<?php

namespace App\Listeners\Purchase;

use App\Events\PurchaseOrderReceived;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPurchaseReceivedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PurchaseOrderReceived $event): void
    {
        $purchaseOrder = $event->purchaseOrder;

        NotificationService::create(
            type: 'purchase_received',
            title: 'Purchase Order Received',
            message: "Purchase order {$purchaseOrder->reference} from {$purchaseOrder->supplier?->name} has been received. Total: " . number_format($purchaseOrder->total, 2),
            notifiable: $purchaseOrder,
            data: [
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'total' => $purchaseOrder->total,
            ]
        );
    }
}
