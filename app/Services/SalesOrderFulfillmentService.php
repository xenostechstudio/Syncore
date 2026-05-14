<?php

namespace App\Services;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;

/**
 * Single source of truth for the quantity_invoiced / quantity_delivered
 * counters stored on SalesOrderItem.
 *
 * The columns are kept (not converted to PHP-computed accessors) because
 * index pages run SQL-level filters like
 *   whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_invoiced'))
 * and rewriting every one as a correlated subquery would be more risk than
 * benefit. Instead, we centralise the recompute formula here and call it
 * from Eloquent observers — so writes that previously had to remember to
 * increment() the column now happen automatically from the underlying
 * invoice / delivery data.
 */
class SalesOrderFulfillmentService
{
    public function recomputeForSalesOrder(SalesOrder $order): void
    {
        $order->loadMissing(['items', 'invoices.items', 'deliveryOrders.items']);

        foreach ($order->items as $item) {
            $this->recomputeForSalesOrderItem($item, $order);
        }
    }

    public function recomputeForSalesOrderItem(SalesOrderItem $item, ?SalesOrder $order = null): void
    {
        $order = $order ?? $item->salesOrder;

        if (! $order) {
            return;
        }

        $invoiced = $this->computeInvoicedQty($order, $item);
        $delivered = $this->computeDeliveredQty($order, $item);

        if ((int) $item->quantity_invoiced === $invoiced
            && (int) $item->quantity_delivered === $delivered) {
            return;
        }

        SalesOrderItem::query()
            ->where('id', $item->id)
            ->update([
                'quantity_invoiced' => $invoiced,
                'quantity_delivered' => $delivered,
            ]);

        $item->quantity_invoiced = $invoiced;
        $item->quantity_delivered = $delivered;
    }

    /**
     * Sum invoiced quantity for one SO item. invoice_items has no
     * sales_order_item_id column, so attribute by product across
     * non-cancelled invoices linked to this SO. Capped at the SO line
     * quantity to avoid over-counting when multiple SO items share a
     * product.
     */
    public function computeInvoicedQty(SalesOrder $order, SalesOrderItem $item): int
    {
        $invoicedForProduct = InvoiceItem::query()
            ->whereHas('invoice', function ($q) use ($order) {
                $q->where('sales_order_id', $order->id)
                    ->where('status', '!=', 'cancelled');
            })
            ->where('product_id', $item->product_id)
            ->sum('quantity');

        return (int) min($invoicedForProduct, $item->quantity);
    }

    /**
     * Sum delivered quantity for one SO item from DELIVERED delivery
     * orders only. Delivery items carry sales_order_item_id reliably so
     * we match directly on it.
     */
    public function computeDeliveredQty(SalesOrder $order, SalesOrderItem $item): int
    {
        $delivered = 0;
        foreach ($order->deliveryOrders as $do) {
            if ($do->status !== 'delivered') {
                continue;
            }
            foreach ($do->items as $doItem) {
                if ($doItem->sales_order_item_id === $item->id) {
                    $delivered += (int) $doItem->quantity_delivered;
                }
            }
        }
        return (int) min($delivered, $item->quantity);
    }
}
