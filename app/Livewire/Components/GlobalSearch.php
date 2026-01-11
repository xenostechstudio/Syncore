<?php

namespace App\Livewire\Components;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class GlobalSearch extends Component
{
    public string $query = '';
    public bool $isOpen = false;
    public array $results = [];

    public function placeholder(): string
    {
        return <<<'HTML'
        <div></div>
        HTML;
    }

    #[On('openGlobalSearch')]
    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->search();
    }

    public function search(): void
    {
        $query = trim($this->query);
        $results = [];

        // Run simpler queries without orWhereHas (much faster)
        // Search by primary field only, not by related model names
        
        // Sales Orders - search by order_number only
        $orders = SalesOrder::query()
            ->select(['id', 'order_number', 'customer_id', 'total'])
            ->with(['customer:id,name'])
            ->where('order_number', 'ilike', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($orders as $order) {
            $results[] = [
                'type' => 'order',
                'icon' => 'shopping-cart',
                'color' => 'amber',
                'title' => $order->order_number,
                'subtitle' => $order->customer?->name ?? 'No customer',
                'meta' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'url' => route('sales.orders.edit', $order->id),
            ];
        }

        // Invoices - search by invoice_number only
        $invoices = Invoice::query()
            ->select(['id', 'invoice_number', 'customer_id', 'status'])
            ->with(['customer:id,name'])
            ->where('invoice_number', 'ilike', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($invoices as $invoice) {
            $status = $invoice->status;
            $statusLabel = $status instanceof \BackedEnum ? $status->value : (string) $status;
            
            $results[] = [
                'type' => 'invoice',
                'icon' => 'document-text',
                'color' => 'blue',
                'title' => $invoice->invoice_number,
                'subtitle' => $invoice->customer?->name ?? 'No customer',
                'meta' => ucfirst($statusLabel),
                'url' => route('invoicing.invoices.edit', $invoice->id),
            ];
        }

        // Customers - search by name, email, phone (same table, no joins)
        $customers = Customer::query()
            ->select(['id', 'name', 'email', 'phone', 'city'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('email', 'ilike', "%{$query}%")
                  ->orWhere('phone', 'ilike', "%{$query}%");
            })
            ->limit(3)
            ->get();

        foreach ($customers as $customer) {
            $results[] = [
                'type' => 'customer',
                'icon' => 'user',
                'color' => 'emerald',
                'title' => $customer->name,
                'subtitle' => $customer->email ?? $customer->phone ?? 'No contact',
                'meta' => $customer->city ?? '',
                'url' => route('sales.customers.edit', $customer->id),
            ];
        }

        // Products - search by name, sku (same table, no joins)
        $products = Product::query()
            ->select(['id', 'name', 'sku', 'selling_price'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('sku', 'ilike', "%{$query}%");
            })
            ->limit(3)
            ->get();

        foreach ($products as $product) {
            $results[] = [
                'type' => 'product',
                'icon' => 'cube',
                'color' => 'violet',
                'title' => $product->name,
                'subtitle' => $product->sku ?? 'No SKU',
                'meta' => 'Rp ' . number_format($product->selling_price ?? 0, 0, ',', '.'),
                'url' => route('inventory.products.edit', $product->id),
            ];
        }

        // Delivery Orders - search by delivery_number only
        $deliveries = DeliveryOrder::query()
            ->select(['id', 'delivery_number', 'sales_order_id', 'status'])
            ->with(['salesOrder:id,customer_id', 'salesOrder.customer:id,name'])
            ->where('delivery_number', 'ilike', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($deliveries as $delivery) {
            $status = $delivery->status;
            $statusLabel = $status instanceof \BackedEnum 
                ? (method_exists($status, 'label') ? $status->label() : $status->value)
                : (string) $status;
            
            $results[] = [
                'type' => 'delivery',
                'icon' => 'truck',
                'color' => 'cyan',
                'title' => $delivery->delivery_number,
                'subtitle' => $delivery->salesOrder?->customer?->name ?? 'No customer',
                'meta' => $statusLabel,
                'url' => route('delivery.orders.edit', $delivery->id),
            ];
        }

        // Suppliers - search by name, email (same table, no joins)
        $suppliers = Supplier::query()
            ->select(['id', 'name', 'email', 'contact_person'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('email', 'ilike', "%{$query}%");
            })
            ->limit(3)
            ->get();

        foreach ($suppliers as $supplier) {
            $results[] = [
                'type' => 'supplier',
                'icon' => 'building-office',
                'color' => 'orange',
                'title' => $supplier->name,
                'subtitle' => $supplier->email ?? $supplier->contact_person ?? 'No contact',
                'meta' => '',
                'url' => route('purchase.suppliers.edit', $supplier->id),
            ];
        }

        // Purchase RFQs - search by reference only
        $purchaseOrders = PurchaseRfq::query()
            ->select(['id', 'reference', 'supplier_id', 'total'])
            ->with(['supplier:id,name'])
            ->where('reference', 'ilike', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($purchaseOrders as $po) {
            $results[] = [
                'type' => 'purchase',
                'icon' => 'shopping-bag',
                'color' => 'pink',
                'title' => $po->reference,
                'subtitle' => $po->supplier?->name ?? 'No supplier',
                'meta' => 'Rp ' . number_format($po->total ?? 0, 0, ',', '.'),
                'url' => route('purchase.rfq.edit', $po->id),
            ];
        }

        $this->results = $results;
    }

    public function render()
    {
        return view('livewire.components.global-search');
    }
}
