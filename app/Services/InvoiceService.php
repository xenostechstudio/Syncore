<?php

namespace App\Services;

use App\Enums\InvoiceState;
use App\Events\InvoicePaid;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Invoicing\Payment;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;

/**
 * Invoice Service
 * 
 * Centralized business logic for invoice operations.
 * 
 * @package App\Services
 */
class InvoiceService
{
    /**
     * Create an invoice from a sales order.
     *
     * @param SalesOrder $salesOrder
     * @param array $itemQuantities Optional: specific quantities per item [item_id => quantity]
     * @return Invoice
     */
    public function createFromSalesOrder(SalesOrder $salesOrder, array $itemQuantities = []): Invoice
    {
        return DB::transaction(function () use ($salesOrder, $itemQuantities) {
            $invoice = Invoice::create([
                'customer_id' => $salesOrder->customer_id,
                'sales_order_id' => $salesOrder->id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'notes' => $salesOrder->notes,
                'terms' => $salesOrder->terms,
            ]);

            $subtotal = 0;
            $tax = 0;

            foreach ($salesOrder->items as $orderItem) {
                $quantity = $itemQuantities[$orderItem->id] ?? $orderItem->quantity_to_invoice;
                
                if ($quantity <= 0) {
                    continue;
                }

                $lineTotal = $quantity * $orderItem->unit_price;
                $lineTax = $orderItem->tax ? ($lineTotal * $orderItem->tax->rate / 100) : 0;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $orderItem->product_id,
                    'sales_order_item_id' => $orderItem->id,
                    'description' => $orderItem->description,
                    'quantity' => $quantity,
                    'unit_price' => $orderItem->unit_price,
                    'tax_id' => $orderItem->tax_id,
                    'tax_amount' => $lineTax,
                    'discount' => $orderItem->discount,
                    'total' => $lineTotal + $lineTax - ($orderItem->discount ?? 0),
                ]);

                // Update invoiced quantity on sales order item
                $orderItem->increment('quantity_invoiced', $quantity);

                $subtotal += $lineTotal;
                $tax += $lineTax;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax - ($invoice->discount ?? 0),
            ]);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Register a payment for an invoice.
     *
     * @param Invoice $invoice
     * @param float $amount
     * @param string $paymentMethod
     * @param string|null $reference
     * @param string|null $notes
     * @return Payment
     */
    public function registerPayment(
        Invoice $invoice,
        float $amount,
        string $paymentMethod = 'manual',
        ?string $reference = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $paymentMethod, $reference, $notes) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_date' => now(),
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $newPaidAmount = $invoice->paid_amount + $amount;
            $oldStatus = $invoice->status;

            $updateData = [
                'paid_amount' => $newPaidAmount,
            ];

            // Determine new status
            if ($newPaidAmount >= $invoice->total) {
                $updateData['status'] = 'paid';
                $updateData['paid_date'] = now();
            } elseif ($newPaidAmount > 0) {
                $updateData['status'] = 'partial';
            }

            $invoice->update($updateData);

            // Log status change if changed
            if ($oldStatus !== $invoice->status) {
                $invoice->logStatusChange($oldStatus, $invoice->status);
            }

            // Dispatch event if fully paid
            if ($invoice->status === 'paid') {
                InvoicePaid::dispatch($invoice, $payment);
            }

            return $payment;
        });
    }

    /**
     * Send an invoice to the customer.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function send(Invoice $invoice): bool
    {
        if (!$invoice->state->canSend()) {
            return false;
        }

        $oldStatus = $invoice->status;
        $invoice->update(['status' => 'sent']);
        $invoice->logStatusChange($oldStatus, 'sent', 'Invoice sent to customer');

        // TODO: Send email notification to customer

        return true;
    }

    /**
     * Cancel an invoice.
     *
     * @param Invoice $invoice
     * @param string|null $reason
     * @return bool
     */
    public function cancel(Invoice $invoice, ?string $reason = null): bool
    {
        if (!$invoice->state->canCancel()) {
            return false;
        }

        return DB::transaction(function () use ($invoice, $reason) {
            $oldStatus = $invoice->status;

            // Reverse invoiced quantities on sales order items
            foreach ($invoice->items as $item) {
                if ($item->sales_order_item_id) {
                    $item->salesOrderItem?->decrement('quantity_invoiced', $item->quantity);
                }
            }

            $invoice->update(['status' => 'cancelled']);
            $invoice->logStatusChange($oldStatus, 'cancelled', $reason ?? 'Invoice cancelled');

            return true;
        });
    }

    /**
     * Calculate invoice totals.
     *
     * @param Invoice $invoice
     * @return void
     */
    public function recalculateTotals(Invoice $invoice): void
    {
        $subtotal = $invoice->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $tax = $invoice->items->sum('tax_amount');
        $discount = $invoice->discount ?? 0;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax - $discount,
        ]);
    }

    /**
     * Get balance due for an invoice.
     *
     * @param Invoice $invoice
     * @return float
     */
    public function getBalanceDue(Invoice $invoice): float
    {
        return max(0, (float) $invoice->total - (float) $invoice->paid_amount);
    }

    /**
     * Check if invoice is overdue.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function isOverdue(Invoice $invoice): bool
    {
        return $invoice->due_date 
            && $invoice->due_date->isPast() 
            && !in_array($invoice->status, ['paid', 'cancelled']);
    }
}
