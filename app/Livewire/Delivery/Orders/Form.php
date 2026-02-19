<?php

namespace App\Livewire\Delivery\Orders;

use App\Enums\DeliveryOrderState;
use App\Livewire\Concerns\WithNotes;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Delivery\DeliveryReturn;
use App\Models\Delivery\DeliveryReturnItem;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryAdjustmentItem;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Delivery'])]
#[Title('Delivery Order')]
class Form extends Component
{
    use WithNotes;

    public ?int $deliveryId = null;

    // Timestamps
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->deliveryId ? DeliveryOrder::find($this->deliveryId) : null;
    }

    public bool $showReturnModal = false;
    public bool $showStatusModal = false;
    public bool $showForecastModal = false;
    public ?int $forecast_product_id = null;
    public array $forecast_data = [];
    public ?string $pending_status = null;
    public string $status_modal_title = '';
    public string $status_modal_message = '';
    public array $status_modal_summary = [];
    public array $status_modal_lines = [];
    public array $status_modal_shortages = [];
    public bool $status_modal_can_confirm = true;
    public ?int $return_warehouse_id = null;
    public string $return_date = '';
    public string $return_notes = '';
    public array $return_items = [];

    // Header fields
    public ?int $sales_order_id = null;
    public ?int $warehouse_id = null;
    public ?int $user_id = null;
    public string $delivery_date = '';
    public ?string $actual_delivery_date = null;
    public string $status = 'pending';
    public string $shipping_address = '';
    public string $recipient_name = '';
    public string $recipient_phone = '';
    public string $tracking_number = '';
    public string $courier = '';
    public string $notes = '';

    public ?string $delivery_number = null;

    public function mount(?int $id = null): void
    {
        $this->status = DeliveryOrderState::PENDING->value;
        $this->delivery_date = now()->format('Y-m-d');
        $this->user_id = Auth::id();
        $this->return_date = now()->format('Y-m-d');

        if ($id) {
            $this->deliveryId = $id;
            $this->loadDelivery();
        } else {
            // Prefill from query string if provided
            $this->sales_order_id = request()->integer('sales_order_id') ?: null;
            $this->warehouse_id = Warehouse::query()->orderBy('name')->value('id');

            if ($this->sales_order_id) {
                $order = SalesOrder::with('customer')->find($this->sales_order_id);
                if ($order) {
                    $this->shipping_address = $order->shipping_address ?? ($order->customer->address ?? '');
                    $this->recipient_name = $order->customer->name ?? '';
                    $this->recipient_phone = $order->customer->phone ?? '';
                }
            }
        }
    }

    public function cancel(): void
    {
        if (! $this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with('items')->findOrFail($this->deliveryId);

        if ($delivery->status->isTerminal() || $delivery->status === DeliveryOrderState::CANCELLED) {
            session()->flash('error', 'This delivery order cannot be cancelled.');
            return;
        }

        DB::transaction(function () use ($delivery) {
            // Only decrement quantity_delivered if DO was already delivered
            if ($delivery->status === DeliveryOrderState::DELIVERED) {
                foreach ($delivery->items as $deliveryItem) {
                    if ($deliveryItem->sales_order_item_id) {
                        SalesOrderItem::query()
                            ->where('id', $deliveryItem->sales_order_item_id)
                            ->decrement('quantity_delivered', $deliveryItem->quantity_delivered);
                    }
                }
            }

            $delivery->update(['status' => DeliveryOrderState::CANCELLED]);
        });

        $this->status = DeliveryOrderState::CANCELLED->value;

        session()->flash('success', 'Delivery order cancelled successfully.');
    }

    public function openReturnModal(): void
    {
        if (! $this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with(['items.salesOrderItem.product'])
            ->findOrFail($this->deliveryId);

        $this->return_date = now()->format('Y-m-d');
        $this->return_notes = '';
        $this->return_warehouse_id = $delivery->warehouse_id;

        $alreadyReturned = DB::table('delivery_return_items as dri')
            ->join('delivery_returns as dr', 'dr.id', '=', 'dri.delivery_return_id')
            ->where('dr.delivery_order_id', $delivery->id)
            ->where('dr.status', '!=', 'cancelled')
            ->selectRaw('dri.delivery_order_item_id, SUM(dri.quantity) as qty')
            ->groupBy('dri.delivery_order_item_id')
            ->pluck('qty', 'dri.delivery_order_item_id');

        $this->return_items = $delivery->items->map(function ($item) use ($alreadyReturned) {
            $delivered = (int) ($item->quantity_delivered ?? 0);
            $returned = (int) ($alreadyReturned[$item->id] ?? 0);
            $max = max(0, $delivered - $returned);

            return [
                'delivery_order_item_id' => $item->id,
                'product_name' => (string) ($item->salesOrderItem?->product?->name ?? '-'),
                'sku' => (string) ($item->salesOrderItem?->product?->sku ?? ''),
                'delivered' => $delivered,
                'already_returned' => $returned,
                'max' => $max,
                'quantity' => $max > 0 ? 1 : 0,
            ];
        })->toArray();

        $this->showReturnModal = true;
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
    }

    public function openStatusTransitionModal(): void
    {
        if (! $this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with(['warehouse', 'salesOrder.customer', 'items.salesOrderItem.product'])
            ->findOrFail($this->deliveryId);

        $warehouseIdForCheck = $this->warehouse_id ?: $delivery->warehouse_id;
        $warehouseNameForCheck = (string) ($delivery->warehouse?->name ?? '-');
        if ($warehouseIdForCheck && $warehouseIdForCheck !== $delivery->warehouse_id) {
            $warehouseNameForCheck = (string) (Warehouse::query()->whereKey($warehouseIdForCheck)->value('name') ?? $warehouseNameForCheck);
        }

        $currentState = $delivery->status instanceof DeliveryOrderState 
            ? $delivery->status 
            : (DeliveryOrderState::tryFrom((string) $delivery->status) ?? DeliveryOrderState::PENDING);
        $nextState = $currentState->next();
        if (! $nextState) {
            return;
        }

        $this->pending_status = $nextState->value;
        $this->status_modal_title = 'Confirm Status Change';
        $this->status_modal_message = 'Change status to ' . $nextState->label() . '?';
        $this->status_modal_summary = [
            'delivery_number' => (string) ($delivery->delivery_number ?? '-'),
            'customer_name' => (string) ($delivery->salesOrder?->customer?->name ?? '-'),
            'warehouse_name' => $warehouseNameForCheck,
            'courier' => (string) ($delivery->courier ?? ''),
            'tracking_number' => (string) ($delivery->tracking_number ?? ''),
            'next_status' => $nextState->label(),
            'total_qty' => (int) ($delivery->items->sum('quantity_to_deliver') ?? 0),
        ];
        $this->status_modal_shortages = [];
        $this->status_modal_can_confirm = true;

        $this->status_modal_lines = [];

        if ($nextState === DeliveryOrderState::PICKED) {
            $outboundAdjustment = InventoryAdjustment::query()
                ->where('source_delivery_order_id', $delivery->id)
                ->where('adjustment_type', 'decrease')
                ->first();

            if ($outboundAdjustment?->isPosted()) {
                $this->status_modal_message = 'This will mark the delivery as Picked. Warehouse stock out (WH/OUT) has already been posted.';
                $this->showStatusModal = true;
                return;
            }

            $this->status_modal_message = 'This will mark the delivery as Picked and post a stock out from ' . ($warehouseNameForCheck ?: 'the selected warehouse') . '.';

            $productIds = $delivery->items
                ->map(fn($line) => $line->salesOrderItem?->product?->id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $stocks = InventoryStock::query()
                ->where('warehouse_id', $warehouseIdForCheck)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($delivery->items as $line) {
                $product = $line->salesOrderItem?->product;
                if (! $product) {
                    continue;
                }

                $required = (int) ($line->quantity_to_deliver ?? 0);
                $available = (int) (($stocks->get($product->id)?->quantity) ?? 0);
                if ($required > $available) {
                    $this->status_modal_shortages[] = [
                        'product_name' => (string) $product->name,
                        'sku' => (string) ($product->sku ?? ''),
                        'required' => $required,
                        'available' => $available,
                        'shortage' => $required - $available,
                    ];
                }
            }

            if (! empty($this->status_modal_shortages)) {
                $this->status_modal_can_confirm = false;
            }
        }

        if ($nextState === DeliveryOrderState::DELIVERED) {
            $this->status_modal_message = 'This will mark the delivery as Delivered.';
        }

        $this->showStatusModal = true;
    }

    public function closeStatusTransitionModal(): void
    {
        $this->showStatusModal = false;
        $this->pending_status = null;
        $this->status_modal_title = '';
        $this->status_modal_message = '';
        $this->status_modal_summary = [];
        $this->status_modal_lines = [];
        $this->status_modal_shortages = [];
        $this->status_modal_can_confirm = true;
    }

    public function openForecastModal(int $productId): void
    {
        if (! $this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with(['warehouse', 'items.salesOrderItem.product'])->findOrFail($this->deliveryId);

        $warehouseId = $this->warehouse_id ?: $delivery->warehouse_id;
        if (! $warehouseId) {
            session()->flash('error', 'Please select a warehouse first.');
            return;
        }

        $product = Product::query()->find($productId);
        if (! $product) {
            return;
        }

        $warehouseName = (string) ($delivery->warehouse?->name ?? '-');
        if ($warehouseId !== $delivery->warehouse_id) {
            $warehouseName = (string) (Warehouse::query()->whereKey($warehouseId)->value('name') ?? $warehouseName);
        }

        $onHand = (int) (InventoryStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->value('quantity') ?? 0);

        $forecastIn = (int) (DB::table('inventory_adjustment_items as iai')
            ->join('inventory_adjustments as ia', 'ia.id', '=', 'iai.inventory_adjustment_id')
            ->where('ia.warehouse_id', $warehouseId)
            ->whereNull('ia.posted_at')
            ->where('ia.adjustment_type', 'increase')
            ->whereNotIn('ia.status', ['cancelled'])
            ->where('iai.product_id', $productId)
            ->selectRaw('COALESCE(SUM(iai.counted_quantity), 0) as qty')
            ->value('qty') ?? 0);

        $thisDoQty = (int) $delivery->items
            ->filter(fn($line) => (int) ($line->salesOrderItem?->product?->id ?? 0) === $productId)
            ->sum(fn($line) => (int) ($line->quantity_to_deliver ?? 0));

        $forecastOut = (int) (DB::table('delivery_order_items as doi')
            ->join('delivery_orders as do', 'do.id', '=', 'doi.delivery_order_id')
            ->join('sales_order_items as soi', 'soi.id', '=', 'doi.sales_order_item_id')
            ->where('do.warehouse_id', $warehouseId)
            ->whereNotIn('do.status', ['delivered', 'returned'])
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('inventory_adjustments as ia2')
                    ->whereColumn('ia2.source_delivery_order_id', 'do.id')
                    ->whereNotNull('ia2.posted_at');
            })
            ->where('soi.product_id', $productId)
            ->selectRaw('COALESCE(SUM(doi.quantity_to_deliver), 0) as qty')
            ->value('qty') ?? 0);

        $reservations = DB::table('delivery_order_items as doi')
            ->join('delivery_orders as do', 'do.id', '=', 'doi.delivery_order_id')
            ->join('sales_order_items as soi', 'soi.id', '=', 'doi.sales_order_item_id')
            ->leftJoin('inventory_adjustments as ia', function ($join) {
                $join->on('ia.source_delivery_order_id', '=', 'do.id')
                    ->where('ia.adjustment_type', '=', 'decrease');
            })
            ->where('do.warehouse_id', $warehouseId)
            ->whereNotIn('do.status', ['delivered', 'returned'])
            ->where('soi.product_id', $productId)
            ->selectRaw('do.id as delivery_order_id, do.delivery_number, do.status, COALESCE(SUM(doi.quantity_to_deliver), 0) as qty, MAX(CASE WHEN ia.posted_at IS NULL THEN 0 ELSE 1 END) as has_posted_whout')
            ->groupBy('do.id', 'do.delivery_number', 'do.status')
            ->orderByDesc('do.id')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'delivery_order_id' => (int) $row->delivery_order_id,
                'delivery_number' => (string) ($row->delivery_number ?? ''),
                'status' => (string) ($row->status ?? ''),
                'qty' => (int) ($row->qty ?? 0),
                'has_posted_whout' => (bool) ($row->has_posted_whout ?? false),
            ])
            ->all();

        $outboundAdjustment = InventoryAdjustment::query()
            ->where('source_delivery_order_id', $delivery->id)
            ->where('adjustment_type', 'decrease')
            ->first();

        $outboundItemQty = null;
        if ($outboundAdjustment) {
            $outboundItemQty = (int) (InventoryAdjustmentItem::query()
                ->where('inventory_adjustment_id', $outboundAdjustment->id)
                ->where('product_id', $productId)
                ->value('counted_quantity') ?? 0);
        }

        $whOutHistory = DB::table('inventory_adjustment_items as iai')
            ->join('inventory_adjustments as ia', 'ia.id', '=', 'iai.inventory_adjustment_id')
            ->where('ia.warehouse_id', $warehouseId)
            ->where('ia.adjustment_type', 'decrease')
            ->whereNotIn('ia.status', ['cancelled'])
            ->where('iai.product_id', $productId)
            ->selectRaw('ia.id, ia.adjustment_number, ia.status, ia.posted_at, ia.reason, COALESCE(SUM(iai.counted_quantity), 0) as qty')
            ->groupBy('ia.id', 'ia.adjustment_number', 'ia.status', 'ia.posted_at', 'ia.reason')
            ->orderByDesc('ia.id')
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'adjustment_number' => (string) ($row->adjustment_number ?? ''),
                'status' => (string) ($row->status ?? ''),
                'posted_at' => $row->posted_at ? (string) $row->posted_at : null,
                'reason' => (string) ($row->reason ?? ''),
                'qty' => (int) ($row->qty ?? 0),
            ])
            ->all();

        $this->forecast_product_id = $productId;
        $this->forecast_data = [
            'warehouse_id' => (int) $warehouseId,
            'warehouse_name' => $warehouseName,
            'product_id' => (int) $productId,
            'product_name' => (string) ($product->name ?? '-'),
            'sku' => (string) ($product->sku ?? ''),
            'on_hand' => $onHand,
            'forecast_in' => $forecastIn,
            'forecast_out' => $forecastOut,
            'available' => $onHand + $forecastIn - $forecastOut,
            'this_do_qty' => $thisDoQty,
            'reservations' => $reservations,
            'wh_out_history' => $whOutHistory,
            'outbound_adjustment' => $outboundAdjustment ? [
                'id' => (int) $outboundAdjustment->id,
                'adjustment_number' => (string) ($outboundAdjustment->adjustment_number ?? ''),
                'status' => (string) ($outboundAdjustment->status ?? ''),
                'posted_at' => $outboundAdjustment->posted_at?->format('Y-m-d H:i:s'),
                'item_qty' => $outboundItemQty,
            ] : null,
        ];

        $this->showForecastModal = true;
    }

    public function closeForecastModal(): void
    {
        $this->showForecastModal = false;
        $this->forecast_product_id = null;
        $this->forecast_data = [];
    }

    public function confirmStatusTransition(): void
    {
        if (! $this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with(['warehouse', 'items.salesOrderItem.product'])->findOrFail($this->deliveryId);
        $currentState = $delivery->status instanceof DeliveryOrderState 
            ? $delivery->status 
            : (DeliveryOrderState::tryFrom((string) $delivery->status) ?? DeliveryOrderState::PENDING);
        $nextState = $currentState->next();
        if (! $nextState) {
            return;
        }

        $warehouseIdForCheck = $this->warehouse_id ?: $delivery->warehouse_id;

        if ($this->pending_status !== $nextState->value) {
            session()->flash('error', 'Status changed. Please try again.');
            $this->closeStatusTransitionModal();
            $this->loadDelivery();
            return;
        }

        if ($nextState === DeliveryOrderState::PICKED) {
            $outboundAdjustment = InventoryAdjustment::query()
                ->where('source_delivery_order_id', $delivery->id)
                ->where('adjustment_type', 'decrease')
                ->first();

            if (! ($outboundAdjustment?->isPosted())) {
                if (! $warehouseIdForCheck) {
                    session()->flash('error', 'Please select a warehouse before marking as Picked.');
                    $this->closeStatusTransitionModal();
                    return;
                }

                $productIds = $delivery->items
                    ->map(fn($line) => $line->salesOrderItem?->product?->id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $stocks = InventoryStock::query()
                    ->where('warehouse_id', $warehouseIdForCheck)
                    ->whereIn('product_id', $productIds)
                    ->get()
                    ->keyBy('product_id');

                foreach ($delivery->items as $line) {
                    $product = $line->salesOrderItem?->product;
                    if (! $product) {
                        continue;
                    }
                    $required = (int) ($line->quantity_to_deliver ?? 0);
                    $available = (int) (($stocks->get($product->id)?->quantity) ?? 0);
                    if ($required > $available) {
                        session()->flash('error', 'Insufficient stock for ' . $product->name);
                        $this->closeStatusTransitionModal();
                        return;
                    }
                }
            }
        }

        $this->status = $nextState->value;
        $postWarehouseOut = $nextState === DeliveryOrderState::PICKED;
        $markDelivered = $nextState === DeliveryOrderState::DELIVERED;
        if ($markDelivered) {
            $this->actual_delivery_date = $this->actual_delivery_date ?: now()->format('Y-m-d');
        }

        try {
            $delivery = $this->persist(postWarehouseOut: $postWarehouseOut);

            if ($markDelivered) {
                // Update quantity_delivered on DO items
                DeliveryOrderItem::query()
                    ->where('delivery_order_id', $delivery->id)
                    ->update([
                        'quantity_delivered' => DB::raw('quantity_to_deliver'),
                    ]);

                // Update quantity_delivered on Sales Order items
                $delivery->load('items');
                foreach ($delivery->items as $doItem) {
                    if ($doItem->sales_order_item_id) {
                        SalesOrderItem::query()
                            ->where('id', $doItem->sales_order_item_id)
                            ->increment('quantity_delivered', $doItem->quantity_to_deliver);
                    }
                }
            }

            $this->closeStatusTransitionModal();

            $this->delivery_number = $delivery->delivery_number;
            session()->flash('success', 'Status updated successfully.');
            $this->redirect(route('delivery.orders.edit', $delivery->id), navigate: true);
        } catch (\Throwable $e) {
            $this->closeStatusTransitionModal();
            $this->loadDelivery();
            session()->flash('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    public function createReturn(): void
    {
        try {
            if (! $this->deliveryId) {
                return;
            }

            $this->validate([
                'return_warehouse_id' => 'required|exists:warehouses,id',
                'return_date' => 'required|date',
                'return_items' => 'required|array|min:1',
                'return_items.*.delivery_order_item_id' => 'required|exists:delivery_order_items,id',
                'return_items.*.quantity' => 'required|integer|min:0',
            ]);

            $delivery = DeliveryOrder::with(['items.salesOrderItem.product'])->findOrFail($this->deliveryId);

            $alreadyReturned = DB::table('delivery_return_items as dri')
                ->join('delivery_returns as dr', 'dr.id', '=', 'dri.delivery_return_id')
                ->where('dr.delivery_order_id', $delivery->id)
                ->where('dr.status', '!=', 'cancelled')
                ->selectRaw('dri.delivery_order_item_id, SUM(dri.quantity) as qty')
                ->groupBy('dri.delivery_order_item_id')
                ->pluck('qty', 'dri.delivery_order_item_id');

            $linesById = $delivery->items->keyBy('id');

            $valid = collect($this->return_items)
                ->map(function ($row) use ($linesById, $alreadyReturned) {
                    $lineId = (int) ($row['delivery_order_item_id'] ?? 0);
                    $qty = (int) ($row['quantity'] ?? 0);
                    $line = $linesById->get($lineId);
                    if (! $line) {
                        return null;
                    }

                    $delivered = (int) ($line->quantity_delivered ?? 0);
                    $returned = (int) ($alreadyReturned[$lineId] ?? 0);
                    $max = max(0, $delivered - $returned);

                    if ($qty <= 0) {
                        return null;
                    }

                    if ($qty > $max) {
                        throw new \RuntimeException('Return quantity exceeds delivered quantity.');
                    }

                    return [
                        'delivery_order_item_id' => $lineId,
                        'quantity' => $qty,
                    ];
                })
                ->filter()
                ->values();

            if ($valid->isEmpty()) {
                throw new \RuntimeException('Please input at least one return quantity.');
            }

            DB::transaction(function () use ($delivery, $valid) {
                $ret = DeliveryReturn::create([
                    'return_number' => DeliveryReturn::generateReturnNumber(),
                    'delivery_order_id' => $delivery->id,
                    'warehouse_id' => $this->return_warehouse_id,
                    'return_date' => $this->return_date,
                    'status' => 'draft',
                    'notes' => $this->return_notes ?: null,
                ]);

                foreach ($valid as $row) {
                    DeliveryReturnItem::create([
                        'delivery_return_id' => $ret->id,
                        'delivery_order_item_id' => $row['delivery_order_item_id'],
                        'quantity' => $row['quantity'],
                    ]);
                }
            });

            $this->showReturnModal = false;
            session()->flash('success', 'Return created successfully.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to create return: ' . $e->getMessage());
        }
    }

    public function receiveReturn(int $returnId): void
    {
        try {
            DB::transaction(function () use ($returnId) {
                $ret = DeliveryReturn::query()
                    ->with(['items.deliveryOrderItem.salesOrderItem.product'])
                    ->lockForUpdate()
                    ->findOrFail($returnId);

                if ($ret->status === 'cancelled') {
                    return;
                }

                $adjustment = InventoryAdjustment::query()
                    ->where('source_delivery_return_id', $ret->id)
                    ->first();

                if (! $adjustment) {
                    $adjustment = InventoryAdjustment::create([
                        'warehouse_id' => $ret->warehouse_id,
                        'user_id' => Auth::id(),
                        'adjustment_date' => $ret->return_date,
                        'adjustment_type' => 'increase',
                        'status' => 'draft',
                        'source_delivery_return_id' => $ret->id,
                        'reason' => $ret->return_number,
                    ]);
                }

                if ($adjustment->isPosted()) {
                    if ($ret->status !== 'received') {
                        $ret->update([
                            'status' => 'received',
                            'received_at' => $ret->received_at ?: now(),
                        ]);
                    }
                    return;
                }

                $adjustment->items()->delete();

                $productIds = $ret->items
                    ->map(fn($line) => $line->deliveryOrderItem?->salesOrderItem?->product?->id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $stocks = InventoryStock::query()
                    ->where('warehouse_id', $ret->warehouse_id)
                    ->whereIn('product_id', $productIds)
                    ->get()
                    ->keyBy('product_id');

                foreach ($ret->items as $line) {
                    $product = $line->deliveryOrderItem?->salesOrderItem?->product;
                    if (! $product) {
                        continue;
                    }

                    $qty = (int) ($line->quantity ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $systemQty = (int) (($stocks->get($product->id)?->quantity) ?? 0);

                    InventoryAdjustmentItem::create([
                        'inventory_adjustment_id' => $adjustment->id,
                        'product_id' => $product->id,
                        'system_quantity' => $systemQty,
                        'counted_quantity' => $qty,
                        'difference' => $qty,
                    ]);
                }

                $adjustment->post();

                $ret->update([
                    'status' => 'received',
                    'received_at' => now(),
                ]);
            });

            session()->flash('success', 'Return received and stock updated.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to receive return: ' . $e->getMessage());
        }
    }

    protected function loadDelivery(): void
    {
        $delivery = DeliveryOrder::with(['salesOrder.customer', 'warehouse'])
            ->findOrFail($this->deliveryId);

        $this->sales_order_id = $delivery->sales_order_id;
        $this->warehouse_id = $delivery->warehouse_id;
        $this->user_id = $delivery->user_id;
        $this->delivery_date = $delivery->delivery_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->actual_delivery_date = $delivery->actual_delivery_date?->format('Y-m-d');
        $this->status = $delivery->status->value;
        $this->shipping_address = $delivery->shipping_address ?? '';
        $this->recipient_name = $delivery->recipient_name ?? '';
        $this->recipient_phone = $delivery->recipient_phone ?? '';
        $this->tracking_number = $delivery->tracking_number ?? '';
        $this->courier = $delivery->courier ?? '';
        $this->notes = $delivery->notes ?? '';
        $this->delivery_number = $delivery->delivery_number;
        $this->createdAt = $delivery->created_at->format('M d, Y \a\t H:i');
        $this->updatedAt = $delivery->updated_at->format('M d, Y \a\t H:i');
    }

    public function save(): void
    {
        $delivery = $this->persist(postWarehouseOut: false);

        $this->delivery_number = $delivery->delivery_number;

        if ($this->deliveryId) {
            session()->flash('success', 'Delivery order updated successfully.');
        } else {
            session()->flash('success', 'Delivery order created successfully.');
            $this->redirect(route('delivery.orders.edit', $delivery->id), navigate: true);
        }
    }

    public function deliver(): void
    {
        $this->openStatusTransitionModal();
    }

    private function persist(bool $postWarehouseOut): DeliveryOrder
    {
        $this->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_date' => 'required|date',
            'status' => ['required', Rule::in(DeliveryOrderState::values())],
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string|max:1000',
            'courier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($postWarehouseOut) {
            $data = [
                'sales_order_id' => $this->sales_order_id,
                'warehouse_id' => $this->warehouse_id,
                'user_id' => $this->user_id ?: Auth::id(),
                'delivery_date' => $this->delivery_date,
                'actual_delivery_date' => $this->actual_delivery_date ?: null,
                'status' => $this->status,
                'shipping_address' => (string) ($this->shipping_address ?? ''),
                'recipient_name' => $this->recipient_name,
                'recipient_phone' => $this->recipient_phone ?: null,
                'tracking_number' => $this->tracking_number ?: null,
                'courier' => $this->courier ?: null,
                'notes' => $this->notes ?: null,
            ];

            if ($this->deliveryId) {
                $delivery = DeliveryOrder::findOrFail($this->deliveryId);
                $delivery->update($data);
            } else {
                $delivery = DeliveryOrder::create($data);
                $this->deliveryId = $delivery->id;
            }

            if ($postWarehouseOut) {
                $this->postWarehouseOutForDelivery($delivery);
            }

            return $delivery;
        });
    }

    private function postWarehouseOutForDelivery(DeliveryOrder $delivery): void
    {
        $delivery->loadMissing(['items.salesOrderItem.product']);

        $adjustment = InventoryAdjustment::query()
            ->where('source_delivery_order_id', $delivery->id)
            ->first();

        if (! $adjustment) {
            $adjustment = InventoryAdjustment::create([
                'warehouse_id' => $delivery->warehouse_id,
                'user_id' => Auth::id(),
                'adjustment_date' => $delivery->delivery_date,
                'adjustment_type' => 'decrease',
                'status' => 'draft',
                'source_delivery_order_id' => $delivery->id,
                'reason' => $delivery->delivery_number,
            ]);
        }

        if ($adjustment->isPosted()) {
            return;
        }

        $adjustment->items()->delete();

        $productIds = $delivery->items
            ->map(fn($line) => $line->salesOrderItem?->product?->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $stocks = InventoryStock::query()
            ->where('warehouse_id', $delivery->warehouse_id)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        foreach ($delivery->items as $line) {
            $product = $line->salesOrderItem?->product;
            if (! $product) {
                continue;
            }

            $qty = (int) ($line->quantity_to_deliver ?? 0);

            $systemQty = (int) (($stocks->get($product->id)?->quantity) ?? 0);

            InventoryAdjustmentItem::create([
                'inventory_adjustment_id' => $adjustment->id,
                'product_id' => $product->id,
                'system_quantity' => $systemQty,
                'counted_quantity' => $qty,
                'difference' => -1 * $qty,
            ]);
        }

        $adjustment->post();
    }

    public function duplicate(): void
    {
        if (!$this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with(['items.salesOrderItem'])->findOrFail($this->deliveryId);

        $newDelivery = DB::transaction(function () use ($delivery) {
            $newDelivery = DeliveryOrder::create([
                'sales_order_id' => $delivery->sales_order_id,
                'warehouse_id' => $delivery->warehouse_id,
                'user_id' => Auth::id(),
                'delivery_date' => now()->format('Y-m-d'),
                'status' => DeliveryOrderState::PENDING->value,
                'shipping_address' => $delivery->shipping_address,
                'recipient_name' => $delivery->recipient_name,
                'recipient_phone' => $delivery->recipient_phone,
                'courier' => $delivery->courier,
                'notes' => $delivery->notes,
            ]);

            foreach ($delivery->items as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $newDelivery->id,
                    'sales_order_item_id' => $item->sales_order_item_id,
                    'quantity_to_deliver' => $item->quantity_to_deliver,
                    'quantity_delivered' => 0,
                ]);
            }

            return $newDelivery;
        });

        session()->flash('success', 'Delivery order duplicated successfully.');
        $this->redirect(route('delivery.orders.edit', $newDelivery->id), navigate: true);
    }

    public function delete(): void
    {
        if ($this->deliveryId) {
            DeliveryOrder::destroy($this->deliveryId);
            session()->flash('success', 'Delivery order deleted successfully.');
            $this->redirect(route('delivery.orders.index'), navigate: true);
        }
    }

    public function render()
    {
        $salesOrders = SalesOrder::with('customer')
            ->orderByDesc('order_date')
            ->limit(50)
            ->get();

        $warehouses = Warehouse::orderBy('name')->get();

        $delivery = $this->deliveryId
            ? DeliveryOrder::with(['salesOrder.customer', 'items.salesOrderItem.product'])
                ->find($this->deliveryId)
            : null;

        $returns = $this->deliveryId
            ? DeliveryReturn::query()
                ->with(['warehouse', 'items.deliveryOrderItem.salesOrderItem.product'])
                ->where('delivery_order_id', $this->deliveryId)
                ->orderByDesc('id')
                ->get()
            : collect();

        $outboundAdjustment = $this->deliveryId
            ? InventoryAdjustment::query()
                ->where('source_delivery_order_id', $this->deliveryId)
                ->where('adjustment_type', 'decrease')
                ->first()
            : null;

        return view('livewire.delivery.orders.form', [
            'salesOrders' => $salesOrders,
            'warehouses' => $warehouses,
            'delivery' => $delivery,
            'returns' => $returns,
            'outboundAdjustment' => $outboundAdjustment,
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
