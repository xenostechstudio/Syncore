<?php

namespace App\Services;

use App\Enums\PurchaseOrderState;
use App\Enums\PurchaseReceiptState;
use App\Models\Inventory\InventoryStock;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseReceiptService
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    /**
     * Validate a draft GRN: bump RFQ item received quantities, move stock,
     * and transition the parent PO to PARTIALLY_RECEIVED or RECEIVED.
     */
    public function validate(PurchaseReceipt $receipt): bool
    {
        if (! $receipt->state->canValidate()) {
            return false;
        }

        return DB::transaction(function () use ($receipt) {
            $receipt->loadMissing(['items.rfqItem', 'purchaseRfq.items']);

            $this->guardAgainstOverReceiving($receipt);

            $productIds = [];

            foreach ($receipt->items as $line) {
                $qty = (float) $line->quantity_received;
                if ($qty <= 0) {
                    continue;
                }

                if ($line->rfqItem) {
                    $line->rfqItem->increment('quantity_received', $qty);
                }

                $this->incrementStock($receipt->warehouse_id, $line->product_id, $qty);

                if ($line->product_id) {
                    $productIds[$line->product_id] = true;
                }
            }

            foreach (array_keys($productIds) as $productId) {
                $this->inventory->recalculateProductQuantity($productId);
            }

            $receipt->received_at = $receipt->received_at ?? now();
            $receipt->received_by = $receipt->received_by ?? auth()->id();
            $receipt->save();

            $receipt->transitionTo(PurchaseReceiptState::VALIDATED);

            $this->syncRfqStateFrom($receipt->purchaseRfq->fresh('items'));

            return true;
        });
    }

    /**
     * Cancel a GRN. If it was previously VALIDATED, reverse the stock
     * movement and decrement RFQ received counters.
     */
    public function cancel(PurchaseReceipt $receipt): bool
    {
        if (! $receipt->state->canCancel()) {
            return false;
        }

        $wasValidated = $receipt->state === PurchaseReceiptState::VALIDATED;

        return DB::transaction(function () use ($receipt, $wasValidated) {
            if ($wasValidated) {
                $receipt->loadMissing(['items.rfqItem', 'purchaseRfq.items']);

                $productIds = [];

                foreach ($receipt->items as $line) {
                    $qty = (float) $line->quantity_received;
                    if ($qty <= 0) {
                        continue;
                    }

                    if ($line->rfqItem) {
                        $newReceived = max(0, (float) $line->rfqItem->quantity_received - $qty);
                        $line->rfqItem->update(['quantity_received' => $newReceived]);
                    }

                    $this->decrementStock($receipt->warehouse_id, $line->product_id, $qty);

                    if ($line->product_id) {
                        $productIds[$line->product_id] = true;
                    }
                }

                foreach (array_keys($productIds) as $productId) {
                    $this->inventory->recalculateProductQuantity($productId);
                }
            }

            $receipt->transitionTo(PurchaseReceiptState::CANCELLED);

            if ($wasValidated) {
                $this->syncRfqStateFrom($receipt->purchaseRfq->fresh('items'));
            }

            return true;
        });
    }

    /**
     * Build a draft GRN prefilled with each RFQ line's outstanding quantity.
     */
    public function buildDraftFor(PurchaseRfq $rfq, int $warehouseId): PurchaseReceipt
    {
        if (! $rfq->state->canReceive()) {
            throw new RuntimeException('Purchase order is not in a receivable state.');
        }

        return DB::transaction(function () use ($rfq, $warehouseId) {
            $receipt = PurchaseReceipt::create([
                'purchase_rfq_id' => $rfq->id,
                'supplier_id' => $rfq->supplier_id,
                'warehouse_id' => $warehouseId,
                'received_by' => auth()->id(),
                'status' => PurchaseReceiptState::DRAFT->value,
            ]);

            foreach ($rfq->items as $rfqItem) {
                $remaining = $rfqItem->quantityRemaining();
                if ($remaining <= 0) {
                    continue;
                }

                $receipt->items()->create([
                    'purchase_rfq_item_id' => $rfqItem->id,
                    'product_id' => $rfqItem->product_id,
                    'quantity_received' => $remaining,
                ]);
            }

            return $receipt->fresh('items');
        });
    }

    private function guardAgainstOverReceiving(PurchaseReceipt $receipt): void
    {
        foreach ($receipt->items as $line) {
            if (! $line->rfqItem) {
                continue;
            }

            $remaining = $line->rfqItem->quantityRemaining();
            $attempted = (float) $line->quantity_received;

            if ($attempted - $remaining > 0.0001) {
                throw new RuntimeException(sprintf(
                    'Cannot receive %s of line %d — only %s remaining on the order.',
                    rtrim(rtrim(number_format($attempted, 2, '.', ''), '0'), '.'),
                    $line->purchase_rfq_item_id,
                    rtrim(rtrim(number_format($remaining, 2, '.', ''), '0'), '.'),
                ));
            }
        }
    }

    private function incrementStock(int $warehouseId, ?int $productId, float $qty): void
    {
        if (! $productId) {
            return;
        }

        $stock = InventoryStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            InventoryStock::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $qty,
            ]);
            return;
        }

        $stock->increment('quantity', $qty);
    }

    private function decrementStock(int $warehouseId, ?int $productId, float $qty): void
    {
        if (! $productId) {
            return;
        }

        $stock = InventoryStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            return;
        }

        $newQty = max(0, (float) $stock->quantity - $qty);
        $stock->update(['quantity' => $newQty]);
    }

    private function syncRfqStateFrom(PurchaseRfq $rfq): void
    {
        if ($rfq->isFullyReceived()) {
            if ($rfq->state !== PurchaseOrderState::RECEIVED && $rfq->state->canReceive()) {
                $rfq->markAsReceived();
            }
            return;
        }

        if ($rfq->hasAnyReceived()) {
            if ($rfq->state === PurchaseOrderState::PURCHASE_ORDER) {
                $rfq->markAsPartiallyReceived();
            }
            return;
        }

        // No receipts left — drop back to PURCHASE_ORDER.
        if (in_array($rfq->state, [PurchaseOrderState::PARTIALLY_RECEIVED, PurchaseOrderState::RECEIVED], true)) {
            $rfq->update(['status' => PurchaseOrderState::PURCHASE_ORDER->value]);
        }
    }
}
