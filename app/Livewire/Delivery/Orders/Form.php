<?php

namespace App\Livewire\Delivery\Orders;

use App\Enums\DeliveryOrderState;
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
    public ?int $deliveryId = null;

    public bool $showReturnModal = false;
    public bool $showStatusModal = false;
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

        $currentState = DeliveryOrderState::tryFrom((string) $delivery->status) ?? DeliveryOrderState::PENDING;
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
            'warehouse_name' => (string) ($delivery->warehouse?->name ?? '-'),
            'courier' => (string) ($delivery->courier ?? ''),
            'tracking_number' => (string) ($delivery->tracking_number ?? ''),
            'next_status' => $nextState->label(),
            'total_qty' => (int) ($delivery->items->sum('quantity_to_deliver') ?? 0),
        ];
        $this->status_modal_shortages = [];
        $this->status_modal_can_confirm = true;

        $this->status_modal_lines = [];

        if ($nextState === DeliveryOrderState::DELIVERED) {
            $this->status_modal_message = 'This will mark the delivery as Delivered and post a stock out from ' . ($delivery->warehouse?->name ?? 'the selected warehouse') . '.';

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

    public function confirmStatusTransition(): void
    {
        if (! $this->deliveryId) {
            return;
        }

        $delivery = DeliveryOrder::with(['warehouse', 'items.salesOrderItem.product'])->findOrFail($this->deliveryId);
        $currentState = DeliveryOrderState::tryFrom((string) $delivery->status) ?? DeliveryOrderState::PENDING;
        $nextState = $currentState->next();
        if (! $nextState) {
            return;
        }

        if ($this->pending_status !== $nextState->value) {
            session()->flash('error', 'Status changed. Please try again.');
            $this->closeStatusTransitionModal();
            $this->loadDelivery();
            return;
        }

        if ($nextState === DeliveryOrderState::DELIVERED) {
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
                $required = (int) ($line->quantity_to_deliver ?? 0);
                $available = (int) (($stocks->get($product->id)?->quantity) ?? 0);
                if ($required > $available) {
                    session()->flash('error', 'Insufficient stock for ' . $product->name);
                    $this->closeStatusTransitionModal();
                    return;
                }
            }
        }

        $this->status = $nextState->value;
        $postWarehouseOut = $nextState === DeliveryOrderState::DELIVERED;
        if ($postWarehouseOut) {
            $this->actual_delivery_date = $this->actual_delivery_date ?: now()->format('Y-m-d');
        }

        try {
            $delivery = $this->persist(postWarehouseOut: $postWarehouseOut);

            if ($postWarehouseOut) {
                DeliveryOrderItem::query()
                    ->where('delivery_order_id', $delivery->id)
                    ->update([
                        'quantity_delivered' => DB::raw('quantity_to_deliver'),
                    ]);
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
                        'adjustment_number' => InventoryAdjustment::generateAdjustmentNumber('increase'),
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
        $this->status = $delivery->status;
        $this->shipping_address = $delivery->shipping_address ?? '';
        $this->recipient_name = $delivery->recipient_name ?? '';
        $this->recipient_phone = $delivery->recipient_phone ?? '';
        $this->tracking_number = $delivery->tracking_number ?? '';
        $this->courier = $delivery->courier ?? '';
        $this->notes = $delivery->notes ?? '';
        $this->delivery_number = $delivery->delivery_number;
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
                $data['delivery_number'] = DeliveryOrder::generateDeliveryNumber();
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
                'adjustment_number' => InventoryAdjustment::generateAdjustmentNumber('decrease'),
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
        ]);
    }
}
