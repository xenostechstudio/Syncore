<?php

namespace App\Observers;

use App\Models\Invoicing\Invoice;
use App\Services\SalesOrderFulfillmentService;

/**
 * Recompute invoiced counters on the parent SO when an invoice flips
 * to/from 'cancelled' (a cancelled invoice's items don't count toward
 * fulfillment). The InvoiceItem observer covers the item-level changes;
 * this one covers the parent status change that doesn't touch items.
 */
class InvoiceObserver
{
    public function __construct(private SalesOrderFulfillmentService $service)
    {
    }

    public function updated(Invoice $invoice): void
    {
        if (! $invoice->wasChanged('status')) {
            return;
        }

        $original = $invoice->getOriginal('status');
        $current = $invoice->status;

        if ($original !== 'cancelled' && $current !== 'cancelled') {
            return;
        }

        if (! $invoice->sales_order_id) {
            return;
        }

        $order = $invoice->salesOrder()->first();
        if (! $order) {
            return;
        }
        $this->service->recomputeForSalesOrder($order);
    }

    public function deleted(Invoice $invoice): void
    {
        $this->recompute($invoice);
    }

    public function restored(Invoice $invoice): void
    {
        $this->recompute($invoice);
    }

    private function recompute(Invoice $invoice): void
    {
        if (! $invoice->sales_order_id) {
            return;
        }
        $order = $invoice->salesOrder()->first();
        if (! $order) {
            return;
        }
        $this->service->recomputeForSalesOrder($order);
    }
}
