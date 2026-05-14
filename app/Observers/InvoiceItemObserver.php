<?php

namespace App\Observers;

use App\Models\Invoicing\InvoiceItem;
use App\Services\SalesOrderFulfillmentService;

class InvoiceItemObserver
{
    public function __construct(private SalesOrderFulfillmentService $service)
    {
    }

    public function saved(InvoiceItem $item): void
    {
        $this->recompute($item);
    }

    public function deleted(InvoiceItem $item): void
    {
        $this->recompute($item);
    }

    private function recompute(InvoiceItem $item): void
    {
        $order = $item->invoice?->salesOrder;
        if (! $order) {
            return;
        }
        $this->service->recomputeForSalesOrder($order);
    }
}
