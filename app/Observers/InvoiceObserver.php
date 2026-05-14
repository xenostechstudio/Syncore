<?php

namespace App\Observers;

use App\Models\Invoicing\Invoice;
use App\Services\SalesOrderFulfillmentService;

/**
 * Recompute the parent SO's fulfillment state when an invoice's status
 * crosses a boundary the SO cares about:
 *   - to/from 'cancelled' — a cancelled invoice's items stop counting
 *     toward the invoiced quantity.
 *   - to/from 'paid' — the SO auto-locks to DONE only once every
 *     invoice is paid (see SalesOrderFulfillmentService::shouldLock),
 *     so settling the last invoice has to re-trigger the lock check.
 *
 * The InvoiceItem observer covers item-level quantity changes; this one
 * covers the parent status change that doesn't touch items.
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

        $crossesBoundary = in_array('cancelled', [$original, $current], true)
            || in_array('paid', [$original, $current], true);

        if (! $crossesBoundary) {
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
