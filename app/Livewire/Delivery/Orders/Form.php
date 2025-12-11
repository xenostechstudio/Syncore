<?php

namespace App\Livewire\Delivery\Orders;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Delivery'])]
#[Title('Delivery Order')]
class Form extends Component
{
    public ?int $deliveryId = null;

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
        $this->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_date' => 'required|date',
            'status' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string|max:1000',
            'courier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $data = [
            'sales_order_id' => $this->sales_order_id,
            'warehouse_id' => $this->warehouse_id,
            'user_id' => $this->user_id ?: Auth::id(),
            'delivery_date' => $this->delivery_date,
            'actual_delivery_date' => $this->actual_delivery_date ?: null,
            'status' => $this->status,
            'shipping_address' => $this->shipping_address ?: null,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone ?: null,
            'tracking_number' => $this->tracking_number ?: null,
            'courier' => $this->courier ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->deliveryId) {
            $delivery = DeliveryOrder::findOrFail($this->deliveryId);
            $delivery->update($data);
            $this->delivery_number = $delivery->delivery_number;
            session()->flash('success', 'Delivery order updated successfully.');
        } else {
            $data['delivery_number'] = DeliveryOrder::generateDeliveryNumber();
            $delivery = DeliveryOrder::create($data);
            $this->deliveryId = $delivery->id;
            $this->delivery_number = $delivery->delivery_number;
            session()->flash('success', 'Delivery order created successfully.');

            $this->redirect(route('delivery.orders.edit', $delivery->id), navigate: true);
            return;
        }
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

        return view('livewire.delivery.orders.form', [
            'salesOrders' => $salesOrders,
            'warehouses' => $warehouses,
            'delivery' => $delivery,
        ]);
    }
}
