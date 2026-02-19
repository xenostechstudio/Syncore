<?php

namespace App\Listeners\Sales;

use App\Events\SalesOrderConfirmed;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Reserve inventory when a sales order is confirmed.
 * 
 * This listener logs the confirmation and can be extended
 * to implement actual inventory reservation logic.
 */
class ReserveInventoryOnConfirm implements ShouldQueue
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(SalesOrderConfirmed $event): void
    {
        $salesOrder = $event->salesOrder;

        // Log the confirmation
        ActivityLogService::log(
            'sales_order_confirmed',
            $salesOrder,
            "Sales Order {$salesOrder->order_number} confirmed"
        );

        // TODO: Implement inventory reservation logic if needed
        // This could reserve stock for the order items to prevent overselling
        // foreach ($salesOrder->items as $item) {
        //     InventoryReservation::create([
        //         'product_id' => $item->product_id,
        //         'sales_order_id' => $salesOrder->id,
        //         'quantity' => $item->quantity,
        //     ]);
        // }
    }
}
