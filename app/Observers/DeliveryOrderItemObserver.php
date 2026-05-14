<?php

namespace App\Observers;

use App\Models\Delivery\DeliveryOrderItem;
use App\Services\SalesOrderFulfillmentService;

class DeliveryOrderItemObserver
{
    public function __construct(private SalesOrderFulfillmentService $service)
    {
    }

    public function saved(DeliveryOrderItem $item): void
    {
        $this->recompute($item);
    }

    public function deleted(DeliveryOrderItem $item): void
    {
        $this->recompute($item);
    }

    private function recompute(DeliveryOrderItem $item): void
    {
        $item->loadMissing('deliveryOrder.salesOrder');

        $order = $item->deliveryOrder?->salesOrder;
        if (! $order) {
            return;
        }
        $this->service->recomputeForSalesOrder($order);
    }
}
