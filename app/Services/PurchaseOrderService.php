<?php

namespace App\Services;

use App\Enums\PurchaseOrderState;
use App\Events\PurchaseOrderReceived;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;
use App\Services\Concerns\HasDocumentItems;
use Illuminate\Support\Facades\DB;

/**
 * Purchase Order Service
 * 
 * Centralized business logic for purchase order (RFQ) operations.
 * Handles creation, confirmation, receiving, and billing.
 * 
 * @package App\Services
 */
class PurchaseOrderService
{
    use HasDocumentItems;
    /**
     * Create a new purchase RFQ.
     *
     * @param array $data
     * @param array $items
     * @return PurchaseRfq
     */
    public function create(array $data, array $items): PurchaseRfq
    {
        return DB::transaction(function () use ($data, $items) {
            $purchaseRfq = PurchaseRfq::create([
                'supplier_id' => $data['supplier_id'],
                'supplier_name' => Supplier::find($data['supplier_id'])?->name,
                'user_id' => auth()->id(),
                'order_date' => $data['order_date'] ?? now(),
                'expected_arrival' => $data['expected_arrival'] ?? null,
                'status' => PurchaseOrderState::RFQ->value,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($purchaseRfq, $items);
            $this->recalculateTotals($purchaseRfq);

            return $purchaseRfq->fresh(['items', 'supplier']);
        });
    }

    /**
     * Update a purchase RFQ.
     *
     * @param PurchaseRfq $purchaseRfq
     * @param array $data
     * @param array $items
     * @return PurchaseRfq
     */
    public function update(PurchaseRfq $purchaseRfq, array $data, array $items): PurchaseRfq
    {
        if (!$purchaseRfq->state->canEdit()) {
            throw new \Exception('Cannot edit this purchase order.');
        }

        return DB::transaction(function () use ($purchaseRfq, $data, $items) {
            $purchaseRfq->update([
                'supplier_id' => $data['supplier_id'] ?? $purchaseRfq->supplier_id,
                'supplier_name' => isset($data['supplier_id']) 
                    ? Supplier::find($data['supplier_id'])?->name 
                    : $purchaseRfq->supplier_name,
                'order_date' => $data['order_date'] ?? $purchaseRfq->order_date,
                'expected_arrival' => $data['expected_arrival'] ?? $purchaseRfq->expected_arrival,
                'notes' => $data['notes'] ?? $purchaseRfq->notes,
            ]);

            $this->syncItems($purchaseRfq, $items);
            $this->recalculateTotals($purchaseRfq);

            return $purchaseRfq->fresh(['items', 'supplier']);
        });
    }

    /**
     * Confirm RFQ as Purchase Order.
     *
     * @param PurchaseRfq $purchaseRfq
     * @return bool
     */
    public function confirm(PurchaseRfq $purchaseRfq): bool
    {
        return $purchaseRfq->confirmOrder();
    }

    /**
     * Mark purchase order as received.
     *
     * @param PurchaseRfq $purchaseRfq
     * @param array $receivedQuantities Optional: [item_id => quantity]
     * @return bool
     */
    public function receive(PurchaseRfq $purchaseRfq, array $receivedQuantities = []): bool
    {
        if ($purchaseRfq->status !== PurchaseOrderState::PURCHASE_ORDER->value) {
            return false;
        }

        return DB::transaction(function () use ($purchaseRfq, $receivedQuantities) {
            foreach ($purchaseRfq->items as $item) {
                $quantity = $receivedQuantities[$item->id] ?? $item->quantity;
                $item->update(['quantity_received' => $quantity]);
            }

            // Check if fully received
            $fullyReceived = $purchaseRfq->items->every(
                fn($item) => $item->quantity_received >= $item->quantity
            );

            if ($fullyReceived) {
                $purchaseRfq->received_at = now();
                $purchaseRfq->save();
                $purchaseRfq->markAsReceived();
                PurchaseOrderReceived::dispatch($purchaseRfq);
            }

            return true;
        });
    }

    /**
     * Create vendor bill from purchase order.
     *
     * @param PurchaseRfq $purchaseRfq
     * @param array $itemQuantities Optional: [item_id => quantity]
     * @return VendorBill
     */
    public function createBill(PurchaseRfq $purchaseRfq, array $itemQuantities = []): VendorBill
    {
        if (!in_array($purchaseRfq->status, [
            PurchaseOrderState::PURCHASE_ORDER->value,
            PurchaseOrderState::RECEIVED->value,
        ])) {
            throw new \Exception('Cannot create bill for this purchase order.');
        }

        return DB::transaction(function () use ($purchaseRfq, $itemQuantities) {
            $bill = VendorBill::create([
                'supplier_id' => $purchaseRfq->supplier_id,
                'purchase_rfq_id' => $purchaseRfq->id,
                'bill_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'notes' => $purchaseRfq->notes,
            ]);

            $subtotal = 0;
            $tax = 0;

            foreach ($purchaseRfq->items as $orderItem) {
                $quantity = $itemQuantities[$orderItem->id] ?? $orderItem->quantity_to_bill;

                if ($quantity <= 0) {
                    continue;
                }

                $lineTotal = $quantity * $orderItem->unit_price;
                $lineTax = $orderItem->tax ? ($lineTotal * $orderItem->tax->rate / 100) : 0;

                $bill->items()->create([
                    'product_id' => $orderItem->product_id,
                    'purchase_rfq_item_id' => $orderItem->id,
                    'description' => $orderItem->description,
                    'quantity' => $quantity,
                    'unit_price' => $orderItem->unit_price,
                    'tax_id' => $orderItem->tax_id,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal + $lineTax,
                ]);

                $orderItem->increment('quantity_billed', $quantity);

                $subtotal += $lineTotal;
                $tax += $lineTax;
            }

            $bill->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ]);

            // Update PO status if fully billed
            if ($purchaseRfq->isFullyBilled()) {
                $purchaseRfq->markAsBilled();
            }

            return $bill->fresh(['items', 'supplier']);
        });
    }

    /**
     * Cancel a purchase order.
     *
     * @param PurchaseRfq $purchaseRfq
     * @param string|null $reason
     * @return bool
     */
    public function cancel(PurchaseRfq $purchaseRfq, ?string $reason = null): bool
    {
        return $purchaseRfq->cancelOrder();
    }

    /**
     * Sync items for a purchase order.
     *
     * @param PurchaseRfq $purchaseRfq
     * @param array $items
     * @return void
     */
    protected function syncItems(PurchaseRfq $purchaseRfq, array $items): void
    {
        $existingIds = [];

        foreach ($items as $itemData) {
            if (!empty($itemData['id'])) {
                $item = PurchaseRfqItem::find($itemData['id']);
                if ($item && $item->purchase_rfq_id === $purchaseRfq->id) {
                    $item->update($this->prepareItemData($itemData));
                    $existingIds[] = $item->id;
                }
            } else {
                $item = $purchaseRfq->items()->create($this->prepareItemData($itemData));
                $existingIds[] = $item->id;
            }
        }

        // Delete removed items (only if not received/billed)
        $purchaseRfq->items()
            ->whereNotIn('id', $existingIds)
            ->where('quantity_received', 0)
            ->where('quantity_billed', 0)
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
        $taxRate = 0;

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
            'total' => $lineTotal + $taxAmount,
        ];
    }

    /**
     * Recalculate totals for a purchase order.
     *
     * @param PurchaseRfq $purchaseRfq
     * @return void
     */
    public function recalculateTotals(PurchaseRfq $purchaseRfq): void
    {
        $purchaseRfq->load('items');

        $subtotal = $purchaseRfq->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $tax = $purchaseRfq->items->sum('tax_amount');

        $purchaseRfq->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }
}
