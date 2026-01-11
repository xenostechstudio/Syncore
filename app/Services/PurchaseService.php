<?php

namespace App\Services;

use App\Enums\PurchaseOrderState;
use App\Events\PurchaseOrderReceived;
use App\Events\VendorBillPaid;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillItem;
use Illuminate\Support\Facades\DB;

/**
 * Purchase Service
 * 
 * Centralized business logic for purchase operations.
 */
class PurchaseService
{
    /**
     * Create a new RFQ.
     */
    public function createRfq(array $data, array $items): PurchaseRfq
    {
        return DB::transaction(function () use ($data, $items) {
            $rfq = PurchaseRfq::create([
                'reference' => PurchaseRfq::generateReference(),
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'] ?? now(),
                'expected_arrival' => $data['expected_arrival'] ?? null,
                'status' => PurchaseOrderState::RFQ->value,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($rfq, $items);
            $this->recalculateTotals($rfq);

            return $rfq->fresh(['items', 'supplier']);
        });
    }

    /**
     * Send RFQ to supplier.
     */
    public function sendRfq(PurchaseRfq $rfq): bool
    {
        if (!$rfq->state->canSendRfq()) {
            return false;
        }

        $oldStatus = $rfq->status;
        $rfq->update(['status' => PurchaseOrderState::RFQ_SENT->value]);
        $rfq->logStatusChange($oldStatus, $rfq->status, 'RFQ sent to supplier');

        return true;
    }

    /**
     * Confirm RFQ as Purchase Order.
     */
    public function confirmOrder(PurchaseRfq $rfq): bool
    {
        if (!$rfq->state->canConfirmOrder()) {
            return false;
        }

        $oldStatus = $rfq->status;
        $rfq->update(['status' => PurchaseOrderState::PURCHASE_ORDER->value]);
        $rfq->logStatusChange($oldStatus, $rfq->status, 'Confirmed as Purchase Order');

        return true;
    }

    /**
     * Mark Purchase Order as received.
     */
    public function markReceived(PurchaseRfq $rfq): bool
    {
        if (!$rfq->state->canReceive()) {
            return false;
        }

        $oldStatus = $rfq->status;
        $rfq->update(['status' => PurchaseOrderState::RECEIVED->value]);
        $rfq->logStatusChange($oldStatus, $rfq->status, 'Goods received');

        // Dispatch event for inventory update and notifications
        event(new PurchaseOrderReceived($rfq));

        return true;
    }

    /**
     * Create Vendor Bill from Purchase Order.
     */
    public function createBill(PurchaseRfq $rfq): ?VendorBill
    {
        if (!$rfq->state->canCreateBill()) {
            return null;
        }

        return DB::transaction(function () use ($rfq) {
            $bill = VendorBill::create([
                'supplier_id' => $rfq->supplier_id,
                'purchase_rfq_id' => $rfq->id,
                'bill_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'subtotal' => $rfq->subtotal,
                'tax' => $rfq->tax,
                'total' => $rfq->total,
                'created_by' => auth()->id(),
            ]);

            foreach ($rfq->items as $rfqItem) {
                VendorBillItem::create([
                    'vendor_bill_id' => $bill->id,
                    'product_id' => $rfqItem->product_id,
                    'description' => $rfqItem->description,
                    'quantity' => $rfqItem->quantity,
                    'unit_price' => $rfqItem->unit_price,
                    'tax_amount' => $rfqItem->tax_amount ?? 0,
                    'total' => $rfqItem->total,
                ]);
            }

            // Update RFQ status
            $oldStatus = $rfq->status;
            $rfq->update(['status' => PurchaseOrderState::BILLED->value]);
            $rfq->logStatusChange($oldStatus, $rfq->status, 'Vendor bill created');

            return $bill->fresh(['items', 'supplier']);
        });
    }

    /**
     * Cancel a Purchase RFQ/Order.
     */
    public function cancel(PurchaseRfq $rfq, ?string $reason = null): bool
    {
        if (!$rfq->state->canCancel()) {
            return false;
        }

        $oldStatus = $rfq->status;
        $rfq->update(['status' => PurchaseOrderState::CANCELLED->value]);
        $rfq->logStatusChange($oldStatus, $rfq->status, $reason ?? 'Cancelled');

        return true;
    }

    /**
     * Sync items for a purchase RFQ.
     */
    protected function syncItems(PurchaseRfq $rfq, array $items): void
    {
        $existingIds = [];

        foreach ($items as $itemData) {
            if (!empty($itemData['id'])) {
                $item = PurchaseRfqItem::find($itemData['id']);
                if ($item && $item->purchase_rfq_id === $rfq->id) {
                    $item->update($this->prepareItemData($itemData));
                    $existingIds[] = $item->id;
                }
            } else {
                $item = $rfq->items()->create($this->prepareItemData($itemData));
                $existingIds[] = $item->id;
            }
        }

        $rfq->items()->whereNotIn('id', $existingIds)->delete();
    }

    /**
     * Prepare item data for storage.
     */
    protected function prepareItemData(array $data): array
    {
        $quantity = $data['quantity'] ?? 1;
        $unitPrice = $data['unit_price'] ?? 0;
        $lineTotal = $quantity * $unitPrice;

        return [
            'product_id' => $data['product_id'],
            'description' => $data['description'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $lineTotal,
        ];
    }

    /**
     * Recalculate totals for a purchase RFQ.
     */
    public function recalculateTotals(PurchaseRfq $rfq): void
    {
        $rfq->load('items');
        $subtotal = $rfq->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $tax = $rfq->items->sum('tax_amount');

        $rfq->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }
}
