<?php

namespace App\Console\Commands;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Services\SalesOrderFulfillmentService;
use Illuminate\Console\Command;

/**
 * Recomputes quantity_invoiced and quantity_delivered on every
 * SalesOrderItem from the related Invoice + DeliveryOrder records.
 *
 * Day-to-day, the SalesOrderFulfillmentService observer fan-out keeps
 * these counters in sync automatically — this command exists as a
 * safety net for:
 *   - One-off data repair after seeders / imports that bypass observers
 *     (e.g. DB::table()->insert(), raw migrations).
 *   - Verifying no drift via --dry-run.
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

    public function handle(SalesOrderFulfillmentService $service): int
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
                $expectedInvoiced = $service->computeInvoicedQty($order, $item);
                $expectedDelivered = $service->computeDeliveredQty($order, $item);

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

}
