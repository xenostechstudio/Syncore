<?php

namespace App\Services;

use App\Enums\DeliveryOrderState;
use App\Events\DeliveryCompleted;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;

/**
 * Delivery Service
 * 
 * Centralized business logic for delivery operations.
 */
class DeliveryService
{
    /**
     * Create a delivery order from a sales order.
     */
    public function createFromSalesOrder(SalesOrder $salesOrder, ?int $warehouseId = null): ?DeliveryOrder
    {
        if (!$salesOrder->canCreateDeliveryOrder()) {
            return null;
        }

        return DB::transaction(function () use ($salesOrder, $warehouseId) {
            $deliveryOrder = DeliveryOrder::create([
                'sales_order_id' => $salesOrder->id,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'delivery_date' => now(),
                'status' => DeliveryOrderState::PENDING->value,
                'shipping_address' => $salesOrder->shipping_address,
                'recipient_name' => $salesOrder->customer->contact_person ?? $salesOrder->customer->name,
                'recipient_phone' => $salesOrder->customer->phone,
            ]);

            foreach ($salesOrder->items as $orderItem) {
                $quantityToDeliver = $orderItem->quantity_to_deliver;
                
                if ($quantityToDeliver <= 0) {
                    continue;
                }

                DeliveryOrderItem::create([
                    'delivery_order_id' => $deliveryOrder->id,
                    'sales_order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'description' => $orderItem->description,
                    'quantity' => $quantityToDeliver,
                ]);
            }

            return $deliveryOrder->fresh(['items', 'salesOrder']);
        });
    }

    /**
     * Transition delivery order to next state.
     */
    public function transitionToNext(DeliveryOrder $deliveryOrder): bool
    {
        $nextState = $deliveryOrder->status->next();
        
        if (!$nextState) {
            return false;
        }

        return $this->transitionTo($deliveryOrder, $nextState);
    }

    /**
     * Transition delivery order to a specific state.
     */
    public function transitionTo(DeliveryOrder $deliveryOrder, DeliveryOrderState $state): bool
    {
        $result = $deliveryOrder->transitionTo($state);

        // If delivered, update sales order items and dispatch event
        if ($result && $state === DeliveryOrderState::DELIVERED) {
            $this->handleDeliveryCompleted($deliveryOrder);
        }

        return $result;
    }

    /**
     * Mark delivery as picked.
     */
    public function markPicked(DeliveryOrder $deliveryOrder): bool
    {
        return $deliveryOrder->markAsPicked();
    }

    /**
     * Mark delivery as in transit.
     */
    public function markInTransit(DeliveryOrder $deliveryOrder, ?string $trackingNumber = null, ?string $courier = null): bool
    {
        if ($deliveryOrder->state !== DeliveryOrderState::PICKED) {
            return false;
        }

        $deliveryOrder->update([
            'tracking_number' => $trackingNumber,
            'courier' => $courier,
        ]);

        return $deliveryOrder->markInTransit();
    }

    /**
     * Mark delivery as delivered.
     */
    public function markDelivered(DeliveryOrder $deliveryOrder): bool
    {
        $result = $deliveryOrder->markAsDelivered();
        
        if ($result) {
            $this->handleDeliveryCompleted($deliveryOrder);
        }

        return $result;
    }

    /**
     * Mark delivery as failed.
     */
    public function markFailed(DeliveryOrder $deliveryOrder, ?string $reason = null): bool
    {
        if ($deliveryOrder->state->isTerminal()) {
            return false;
        }

        if ($reason) {
            $deliveryOrder->notes = ($deliveryOrder->notes ? $deliveryOrder->notes . "\n" : '') . "Failed: " . $reason;
        }

        return $deliveryOrder->transitionTo(DeliveryOrderState::FAILED);
    }

    /**
     * Cancel a delivery order.
     */
    public function cancel(DeliveryOrder $deliveryOrder, ?string $reason = null): bool
    {
        return $deliveryOrder->cancelDelivery();
    }

    /**
     * Handle delivery completed - update quantities and dispatch event.
     */
    protected function handleDeliveryCompleted(DeliveryOrder $deliveryOrder): void
    {
        // Update delivered quantities on sales order items
        foreach ($deliveryOrder->items as $item) {
            if ($item->sales_order_item_id) {
                $item->salesOrderItem?->increment('quantity_delivered', $item->quantity);
            }
        }

        // Dispatch event for inventory update and notifications
        DeliveryCompleted::dispatch($deliveryOrder);
    }
}
