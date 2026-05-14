<?php

namespace App\Observers;

use App\Models\Delivery\DeliveryOrder;
use App\Services\SalesOrderFulfillmentService;

/**
 * Recompute delivered counters on the parent SO when a delivery order
 * flips to/from 'delivered' (only DELIVERED rows count toward
 * fulfillment). The DeliveryOrderItem observer covers item-level
 * changes; this one covers the parent status change.
 */
class DeliveryOrderObserver
{
    public function __construct(private SalesOrderFulfillmentService $service)
    {
    }

    public function updated(DeliveryOrder $deliveryOrder): void
    {
        if (! $deliveryOrder->wasChanged('status')) {
            return;
        }

        $original = $deliveryOrder->getOriginal('status');
        $current = $deliveryOrder->status;

        if ($original !== 'delivered' && $current !== 'delivered') {
            return;
        }

        if (! $deliveryOrder->sales_order_id) {
            return;
        }

        $order = $deliveryOrder->salesOrder()->first();
        if (! $order) {
            return;
        }
        $this->service->recomputeForSalesOrder($order);
    }

    public function deleted(DeliveryOrder $deliveryOrder): void
    {
        $this->recompute($deliveryOrder);
    }

    public function restored(DeliveryOrder $deliveryOrder): void
    {
        $this->recompute($deliveryOrder);
    }

    private function recompute(DeliveryOrder $deliveryOrder): void
    {
        if (! $deliveryOrder->sales_order_id) {
            return;
        }
        $order = $deliveryOrder->salesOrder()->first();
        if (! $order) {
            return;
        }
        $this->service->recomputeForSalesOrder($order);
    }
}
