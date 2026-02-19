<?php

namespace App\Services;

use App\Enums\SalesOrderState;
use App\Events\SalesOrderConfirmed;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Services\Concerns\HasDocumentItems;
use Illuminate\Support\Facades\DB;

/**
 * Sales Order Service
 * 
 * Centralized business logic for sales order operations.
 * 
 * @package App\Services
 */
class SalesOrderService
{
    use HasDocumentItems;
    /**
     * Create a new sales order.
     *
     * @param array $data
     * @param array $items
     * @return SalesOrder
     */
    public function create(array $data, array $items): SalesOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $salesOrder = SalesOrder::create([
                'customer_id' => $data['customer_id'],
                'user_id' => auth()->id(),
                'order_date' => $data['order_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => SalesOrderState::QUOTATION->value,
                'payment_terms' => $data['payment_terms'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            $this->syncItems($salesOrder, $items);
            $this->recalculateTotals($salesOrder);

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    /**
     * Update a sales order.
     *
     * @param SalesOrder $salesOrder
     * @param array $data
     * @param array $items
     * @return SalesOrder
     */
    public function update(SalesOrder $salesOrder, array $data, array $items): SalesOrder
    {
        if (!$salesOrder->state->canEdit()) {
            throw new \Exception('Cannot edit this sales order.');
        }

        return DB::transaction(function () use ($salesOrder, $data, $items) {
            $salesOrder->update([
                'customer_id' => $data['customer_id'] ?? $salesOrder->customer_id,
                'order_date' => $data['order_date'] ?? $salesOrder->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $salesOrder->expected_delivery_date,
                'payment_terms' => $data['payment_terms'] ?? $salesOrder->payment_terms,
                'shipping_address' => $data['shipping_address'] ?? $salesOrder->shipping_address,
                'notes' => $data['notes'] ?? $salesOrder->notes,
                'terms' => $data['terms'] ?? $salesOrder->terms,
            ]);

            if ($salesOrder->canEditItems()) {
                $this->syncItems($salesOrder, $items);
            }

            $this->recalculateTotals($salesOrder);

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    /**
     * Confirm a quotation as a sales order.
     *
     * @param SalesOrder $salesOrder
     * @return bool
     */
    public function confirm(SalesOrder $salesOrder): bool
    {
        $result = $salesOrder->confirm();

        if ($result) {
            SalesOrderConfirmed::dispatch($salesOrder);
        }

        return $result;
    }

    /**
     * Send quotation to customer.
     *
     * @param SalesOrder $salesOrder
     * @return bool
     */
    public function sendQuotation(SalesOrder $salesOrder): bool
    {
        if ($salesOrder->state !== SalesOrderState::QUOTATION) {
            return false;
        }

        $result = $salesOrder->transitionTo(SalesOrderState::QUOTATION_SENT);

        // TODO: Send email to customer

        return $result;
    }

    /**
     * Cancel a sales order.
     *
     * @param SalesOrder $salesOrder
     * @param string|null $reason
     * @return bool
     */
    public function cancel(SalesOrder $salesOrder, ?string $reason = null): bool
    {
        return $salesOrder->cancelOrder();
    }

    /**
     * Mark sales order as done.
     *
     * @param SalesOrder $salesOrder
     * @return bool
     */
    public function markAsDone(SalesOrder $salesOrder): bool
    {
        return $salesOrder->lock();
    }

    /**
     * Sync items for a sales order.
     *
     * @param SalesOrder $salesOrder
     * @param array $items
     * @return void
     */
    protected function syncItems(SalesOrder $salesOrder, array $items): void
    {
        $existingIds = [];

        foreach ($items as $itemData) {
            if (!empty($itemData['id'])) {
                // Update existing item
                $item = SalesOrderItem::find($itemData['id']);
                if ($item && $item->sales_order_id === $salesOrder->id) {
                    $item->update($this->prepareItemData($itemData));
                    $existingIds[] = $item->id;
                }
            } else {
                // Create new item
                $item = $salesOrder->items()->create($this->prepareItemData($itemData));
                $existingIds[] = $item->id;
            }
        }

        // Delete removed items (only if not invoiced/delivered)
        $salesOrder->items()
            ->whereNotIn('id', $existingIds)
            ->where('quantity_invoiced', 0)
            ->where('quantity_delivered', 0)
            ->delete();
    }

    /**
     * Prepare item data for storage.
     *
     * @param array $data
     * @return array
     */
    protected function prepareItemData(array $data): array
    {
        $quantity = $data['quantity'] ?? 1;
        $unitPrice = $data['unit_price'] ?? 0;
        $discount = $data['discount'] ?? 0;
        $taxRate = 0;

        // Calculate tax if tax_id provided
        if (!empty($data['tax_id'])) {
            $tax = \App\Models\Sales\Tax::find($data['tax_id']);
            $taxRate = $tax ? $tax->rate : 0;
        }

        $lineTotal = $quantity * $unitPrice;
        $taxAmount = $lineTotal * ($taxRate / 100);

        return [
            'product_id' => $data['product_id'],
            'description' => $data['description'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_id' => $data['tax_id'] ?? null,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total' => $lineTotal + $taxAmount - $discount,
        ];
    }

    /**
     * Recalculate totals for a sales order.
     *
     * @param SalesOrder $salesOrder
     * @return void
     */
    public function recalculateTotals(SalesOrder $salesOrder): void
    {
        $salesOrder->load('items');

        $subtotal = $salesOrder->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $tax = $salesOrder->items->sum('tax_amount');
        $discount = $salesOrder->discount ?? 0;

        $salesOrder->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax - $discount,
        ]);
    }
}
