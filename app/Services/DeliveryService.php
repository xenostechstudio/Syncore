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
    public function createFromSalesOrder(SalesOrder $salesOrder, ?int $warehouseId = null, array $options = []): ?DeliveryOrder
    {
        if (!$salesOrder->canCreateDeliveryOrder()) {
            return null;
        }

        return DB::transaction(function () use ($salesOrder, $warehouseId, $options) {
            $deliveryOrder = DeliveryOrder::create([
                'sales_order_id' => $salesOrder->id,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'delivery_date' => $options['delivery_date'] ?? now(),
                'status' => DeliveryOrderState::PENDING->value,
                'shipping_address' => $options['shipping_address'] ?? $salesOrder->shipping_address,
                'recipient_name' => $options['recipient_name'] ?? ($salesOrder->customer->contact_person ?? $salesOrder->customer->name),
                'recipient_phone' => $options['recipient_phone'] ?? $salesOrder->customer->phone,
                'delivery_instructions' => $options['delivery_instructions'] ?? null,
                'preferred_time_slot' => $options['preferred_time_slot'] ?? null,
                'shipping_cost' => $options['shipping_cost'] ?? null,
                'insurance_amount' => $options['insurance_amount'] ?? null,
                'is_partial' => $options['is_partial'] ?? false,
                'parent_delivery_id' => $options['parent_delivery_id'] ?? null,
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
                    'description' => $orderItem->product->name ?? $orderItem->description,
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
     * Record proof of delivery.
     */
    public function recordProofOfDelivery(DeliveryOrder $deliveryOrder, array $data): bool
    {
        return $deliveryOrder->recordProofOfDelivery($data);
    }

    /**
     * Record customer feedback.
     */
    public function recordCustomerFeedback(DeliveryOrder $deliveryOrder, int $rating, ?string $feedback = null): bool
    {
        return $deliveryOrder->recordCustomerFeedback($rating, $feedback);
    }

    /**
     * Create partial delivery from existing delivery order.
     */
    public function createPartialDelivery(DeliveryOrder $parentDelivery, array $items, array $options = []): ?DeliveryOrder
    {
        if ($parentDelivery->state->isTerminal()) {
            return null;
        }

        return DB::transaction(function () use ($parentDelivery, $items, $options) {
            $partialDelivery = DeliveryOrder::create([
                'sales_order_id' => $parentDelivery->sales_order_id,
                'warehouse_id' => $parentDelivery->warehouse_id,
                'user_id' => auth()->id(),
                'delivery_date' => $options['delivery_date'] ?? now()->addDay(),
                'status' => DeliveryOrderState::PENDING->value,
                'shipping_address' => $parentDelivery->shipping_address,
                'recipient_name' => $parentDelivery->recipient_name,
                'recipient_phone' => $parentDelivery->recipient_phone,
                'delivery_instructions' => $parentDelivery->delivery_instructions,
                'is_partial' => true,
                'parent_delivery_id' => $parentDelivery->id,
            ]);

            foreach ($items as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $partialDelivery->id,
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $partialDelivery->fresh(['items', 'salesOrder']);
        });
    }

    /**
     * Get delivery performance metrics.
     */
    public function getPerformanceMetrics(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $query = DeliveryOrder::where('status', DeliveryOrderState::DELIVERED);

        if ($startDate) {
            $query->where('delivered_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('delivered_at', '<=', $endDate);
        }

        $deliveries = $query->get();
        $total = $deliveries->count();

        if ($total === 0) {
            return [
                'total_deliveries' => 0,
                'on_time_deliveries' => 0,
                'late_deliveries' => 0,
                'on_time_rate' => 0,
                'average_delivery_time' => 0,
                'average_rating' => 0,
                'total_attempts' => 0,
                'first_attempt_success_rate' => 0,
            ];
        }

        $onTime = $deliveries->filter(fn($d) => $d->isOnTime())->count();
        $avgDeliveryTime = $deliveries->avg(fn($d) => $d->getDeliveryDuration());
        $avgRating = $deliveries->whereNotNull('customer_rating')->avg('customer_rating');
        $totalAttempts = $deliveries->sum('delivery_attempts');
        $firstAttemptSuccess = $deliveries->where('delivery_attempts', 1)->count();

        return [
            'total_deliveries' => $total,
            'on_time_deliveries' => $onTime,
            'late_deliveries' => $total - $onTime,
            'on_time_rate' => round(($onTime / $total) * 100, 2),
            'average_delivery_time' => round($avgDeliveryTime ?? 0, 1),
            'average_rating' => round($avgRating ?? 0, 2),
            'total_attempts' => $totalAttempts,
            'first_attempt_success_rate' => $total > 0 ? round(($firstAttemptSuccess / $total) * 100, 2) : 0,
        ];
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
