<?php

namespace App\Console\Commands;

use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recomputes quantity_invoiced and quantity_delivered on every
 * SalesOrderItem from the related Invoice + DeliveryOrder records. The
 * "Create Invoice" and "Create Delivery" buttons on the SO form gate on
 * these stored counters — when seeders or other write paths skip the
 * increment, the buttons stay visible on top of already-fulfilled
 * orders.
 *
 * Idempotent. Safe to re-run.
 *
 * Usage:
 *   php artisan sales-orders:reconcile-fulfillment
 *   php artisan sales-orders:reconcile-fulfillment --dry-run
 *   php artisan sales-orders:reconcile-fulfillment --order=10
 */
class ReconcileSalesOrderFulfillment extends Command
{
    protected $signature = 'sales-orders:reconcile-fulfillment
        {--dry-run : Report changes without writing them}
        {--order= : Reconcile a single sales order by id}';

    protected $description = 'Recompute quantity_invoiced and quantity_delivered on SO items from related invoices and deliveries';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $orderId = $this->option('order') !== null ? (int) $this->option('order') : null;

        $query = SalesOrder::with([
            'items',
            'invoices.items',
            'deliveryOrders.items',
        ]);

        if ($orderId) {
            $query->where('id', $orderId);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No sales orders to reconcile.');
            return Command::SUCCESS;
        }

        $touchedItems = 0;
        $touchedOrders = 0;

        foreach ($orders as $order) {
            $orderChanged = false;

            foreach ($order->items as $item) {
                $expectedInvoiced = $this->computeInvoicedQty($order, $item);
                $expectedDelivered = $this->computeDeliveredQty($order, $item);

                $invoicedDrift = (int) $item->quantity_invoiced !== $expectedInvoiced;
                $deliveredDrift = (int) $item->quantity_delivered !== $expectedDelivered;

                if (! $invoicedDrift && ! $deliveredDrift) {
                    continue;
                }

                $this->line(sprintf(
                    '  SO #%d item #%d  invoiced %d → %d   delivered %d → %d',
                    $order->id,
                    $item->id,
                    $item->quantity_invoiced,
                    $expectedInvoiced,
                    $item->quantity_delivered,
                    $expectedDelivered,
                ));

                if (! $dryRun) {
                    SalesOrderItem::query()
                        ->where('id', $item->id)
                        ->update([
                            'quantity_invoiced' => $expectedInvoiced,
                            'quantity_delivered' => $expectedDelivered,
                        ]);
                }

                $touchedItems++;
                $orderChanged = true;
            }

            if ($orderChanged) {
                $touchedOrders++;
            }
        }

        $this->newLine();
        if ($touchedItems === 0) {
            $this->info('All sales orders are consistent. Nothing to do.');
            return Command::SUCCESS;
        }

        $verb = $dryRun ? 'Would update' : 'Updated';
        $this->info(sprintf('%s %d item(s) across %d order(s).', $verb, $touchedItems, $touchedOrders));
        return Command::SUCCESS;
    }

    /**
     * Sum invoiced quantity for one SO item. invoice_items has no
     * sales_order_item_id column, so we attribute by product across
     * invoices linked to this SO. Capped at the SO item's quantity to
     * avoid over-counting when multiple SO items share a product.
     */
    protected function computeInvoicedQty(SalesOrder $order, SalesOrderItem $item): int
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
     * Sum delivered quantity for one SO item from delivered (non-returned,
     * non-cancelled) DOs. Delivery items already carry sales_order_item_id
     * reliably across both seeded and runtime paths, so this is a direct
     * sum — no fallback needed.
     */
    protected function computeDeliveredQty(SalesOrder $order, SalesOrderItem $item): int
    {
        $delivered = 0;
        foreach ($order->deliveryOrders as $do) {
            if (! in_array($do->status, ['delivered'], true)) {
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
