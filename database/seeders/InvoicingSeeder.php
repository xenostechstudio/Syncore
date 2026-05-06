<?php

namespace Database\Seeders;

use App\Enums\SalesOrderState;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Invoicing\Payment;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Seeder;

/**
 * Builds a representative demo dataset for the Invoicing module.
 *
 * Existing seeders create sales orders but no invoices, so a fresh
 * `php artisan migrate --seed` shows an empty Invoicing dashboard. This
 * seeder fills that gap: ~70% of seeded sales orders get one invoice
 * with a status drawn from a realistic distribution (draft / sent /
 * partial / paid / overdue / cancelled). Paid and partial invoices get
 * matching Payment rows so the timeline + aging report have something
 * to display.
 */
class InvoicingSeeder extends Seeder
{
    public function run(): void
    {
        $salesOrders = SalesOrder::with(['customer', 'items'])
            ->where('status', SalesOrderState::SALES_ORDER->value)
            ->orderBy('id')
            ->get();

        if ($salesOrders->isEmpty()) {
            return;
        }

        // Status distribution per invoice. Order matters — we cycle through
        // this list as we walk the sales orders, skipping ~30% to leave some
        // SOs un-invoiced.
        $statusCycle = ['paid', 'sent', 'partial', 'paid', 'overdue', 'sent', 'draft'];
        $cycleIdx = 0;

        foreach ($salesOrders as $idx => $order) {
            // Skip every ~3rd order so not every SO has an invoice.
            if ($idx % 3 === 2) {
                continue;
            }

            $status = $statusCycle[$cycleIdx % count($statusCycle)];
            $cycleIdx++;

            $invoiceDate = $order->order_date->copy()->addDays(rand(1, 5));
            $dueDate = $invoiceDate->copy()->addDays(30);

            // Overdue invoices need a due_date in the past.
            if ($status === 'overdue') {
                $invoiceDate = now()->subDays(45);
                $dueDate = now()->subDays(15);
            }

            $invoice = Invoice::create([
                'invoice_number' => 'INV' . str_pad((string) ($idx + 1), 5, '0', STR_PAD_LEFT),
                'customer_id'    => $order->customer_id,
                'sales_order_id' => $order->id,
                'user_id'        => $order->user_id,
                'invoice_date'   => $invoiceDate,
                'due_date'       => $dueDate,
                'status'         => $status,
                'subtotal'       => $order->subtotal,
                'tax'            => $order->tax,
                'total'          => $order->total,
                'notes'          => $idx % 4 === 0 ? 'Mohon transfer ke rekening yang tertera.' : null,
            ]);

            // Mirror the order items onto the invoice.
            foreach ($order->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'discount'    => $item->discount,
                    'total'       => $item->total,
                ]);
            }

            // Apply payments for paid / partial states.
            if ($status === 'paid') {
                $this->recordPayment($invoice, $invoice->total, $invoiceDate->copy()->addDays(rand(5, 25)), 1);
                $invoice->update([
                    'paid_amount' => $invoice->total,
                    'paid_date'   => $invoiceDate->copy()->addDays(rand(5, 25)),
                ]);
            } elseif ($status === 'partial') {
                $partial = round($invoice->total * 0.5, 2);
                $this->recordPayment($invoice, $partial, $invoiceDate->copy()->addDays(rand(5, 15)), 1);
                $invoice->update(['paid_amount' => $partial]);
            }
        }
    }

    /**
     * Record a payment row tied to an invoice. Uses a sequence-style number
     * so the payment list reads naturally in the demo UI.
     */
    private function recordPayment(Invoice $invoice, float $amount, $date, int $sequence): void
    {
        $methods = ['Bank Transfer', 'Credit Card', 'Cash', 'Virtual Account'];

        Payment::create([
            'payment_number' => 'PAY' . str_pad((string) $invoice->id, 4, '0', STR_PAD_LEFT) . '-' . $sequence,
            'invoice_id'     => $invoice->id,
            'payment_date'   => $date,
            'amount'         => $amount,
            'payment_method' => $methods[array_rand($methods)],
            'reference'      => 'TRX' . strtoupper(substr(md5((string) $invoice->id . $sequence), 0, 8)),
            'status'         => 'completed',
        ]);
    }
}
