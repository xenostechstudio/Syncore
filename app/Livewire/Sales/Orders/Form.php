<?php

namespace App\Livewire\Sales\Orders;

use App\Models\Inventory\InventoryItem;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Order')]
class Form extends Component
{
    // Order Details
    public ?int $orderId = null;
    public ?int $customer_id = null;
    public string $order_date = '';
    public string $expected_delivery_date = '';
    public string $status = 'draft';
    public string $notes = '';
    public string $terms = '';
    public string $shipping_address = '';
    public ?string $orderNumber = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    // Order Items
    public array $items = [];

    // UI State
    public bool $showCustomerModal = false;
    public bool $showItemModal = false;
    public string $itemSearch = '';

    // History/Activity Log
    public array $activityLog = [];

    public function mount(?int $id = null): void
    {
        $this->order_date = now()->format('Y-m-d');
        $this->expected_delivery_date = now()->addDays(7)->format('Y-m-d');

        if ($id) {
            $this->orderId = $id;
            $this->loadOrder();
        } else {
            // Add one empty item row
            $this->addItem();
        }
    }

    public function loadOrder(): void
    {
        $order = SalesOrder::with(['customer', 'items.inventoryItem'])->findOrFail($this->orderId);

        $this->customer_id = $order->customer_id;
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->expected_delivery_date = $order->expected_delivery_date?->format('Y-m-d') ?? '';
        $this->status = $order->status;
        $this->notes = $order->notes ?? '';
        $this->terms = $order->terms ?? '';
        $this->shipping_address = $order->shipping_address ?? '';
        $this->orderNumber = $order->order_number;
        $this->createdAt = $order->created_at->format('M d, Y \a\t H:i');
        $this->updatedAt = $order->updated_at->format('M d, Y \a\t H:i');

        $this->items = $order->items->map(fn($item) => [
            'id' => $item->id,
            'inventory_item_id' => $item->inventory_item_id,
            'name' => $item->inventoryItem->name ?? '',
            'sku' => $item->inventoryItem->sku ?? '',
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount' => $item->discount,
            'total' => $item->total,
        ])->toArray();

        // Build activity log
        $this->activityLog = [
            [
                'type' => 'created',
                'message' => 'Order created',
                'user' => $order->user->name ?? 'System',
                'date' => $order->created_at->format('M d, Y H:i'),
            ],
        ];

        if ($order->updated_at->gt($order->created_at)) {
            $this->activityLog[] = [
                'type' => 'updated',
                'message' => 'Order updated',
                'user' => $order->user->name ?? 'System',
                'date' => $order->updated_at->format('M d, Y H:i'),
            ];
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'inventory_item_id' => null,
            'name' => '',
            'sku' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount' => 0,
            'total' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectItem(int $index, int $itemId): void
    {
        $item = InventoryItem::find($itemId);
        if ($item) {
            $this->items[$index]['inventory_item_id'] = $item->id;
            $this->items[$index]['name'] = $item->name;
            $this->items[$index]['sku'] = $item->sku;
            $this->items[$index]['unit_price'] = $item->selling_price ?? 0;
            $this->calculateItemTotal($index);
        }
    }

    public function calculateItemTotal(int $index): void
    {
        $item = &$this->items[$index];
        $subtotal = $item['quantity'] * $item['unit_price'];
        $item['total'] = $subtotal - $item['discount'];
    }

    public function updatedItems($value, $key): void
    {
        // Extract index from key like "0.quantity"
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $this->calculateItemTotal($index);
        }
    }

    public function reorderItems(int $from, int $to): void
    {
        if ($from === $to || !isset($this->items[$from])) {
            return;
        }

        $item = $this->items[$from];
        array_splice($this->items, $from, 1);
        array_splice($this->items, $to, 0, [$item]);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum('total');
    }

    public function getTaxProperty(): float
    {
        return $this->subtotal * 0.11; // 11% tax
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax;
    }

    public function save(): void
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $orderData = [
            'customer_id' => $this->customer_id,
            'user_id' => Auth::id(),
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date ?: null,
            'status' => $this->status,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'shipping_address' => $this->shipping_address,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => 0,
            'total' => $this->total,
        ];

        if ($this->orderId) {
            $order = SalesOrder::findOrFail($this->orderId);
            $order->update($orderData);
            $order->items()->delete();
        } else {
            $order = SalesOrder::create($orderData);
            $this->orderId = $order->id;
        }

        foreach ($this->items as $item) {
            if ($item['inventory_item_id']) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                ]);
            }
        }

        session()->flash('success', $this->orderId ? 'Order updated successfully.' : 'Order created successfully.');
        $this->redirect(route('sales.orders.index'), navigate: true);
    }

    public function confirm(): void
    {
        $this->status = 'confirmed';
        $this->save();
    }

    public function cancel(): void
    {
        if ($this->orderId) {
            $order = SalesOrder::findOrFail($this->orderId);
            $order->update(['status' => 'cancelled']);
            
            session()->flash('success', 'Order cancelled successfully.');
            $this->redirect(route('sales.orders.index'), navigate: true);
        }
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $inventoryItems = InventoryItem::query()
            ->when($this->itemSearch, fn($q) => $q->where('name', 'like', "%{$this->itemSearch}%")
                ->orWhere('sku', 'like', "%{$this->itemSearch}%"))
            ->where('status', '!=', 'out_of_stock')
            ->orderBy('name')
            ->limit(20)
            ->get();

        $selectedCustomer = $this->customer_id ? Customer::find($this->customer_id) : null;

        return view('livewire.sales.orders.form', [
            'customers' => $customers,
            'inventoryItems' => $inventoryItems,
            'selectedCustomer' => $selectedCustomer,
        ]);
    }
}
