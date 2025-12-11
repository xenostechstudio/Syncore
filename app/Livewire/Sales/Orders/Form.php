<?php

namespace App\Livewire\Sales\Orders;

use App\Enums\SalesOrderState;
use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Sales\Tax;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public string $pricelist = '';
    public string $payment_terms = '';

    // Order Items
    public array $items = [];

    // UI State
    public bool $showCustomerModal = false;
    public bool $showItemModal = false;
    public bool $showInvoiceModal = false;
    public string $itemSearch = '';

    // Invoice Creation Options
    public string $invoiceType = 'regular'; // regular, down_payment_percentage, down_payment_fixed
    public float $downPaymentPercentage = 0;
    public float $downPaymentAmount = 0;

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
        $order = SalesOrder::with(['customer', 'items.product', 'items.tax'])->findOrFail($this->orderId);

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
            'product_id' => $item->product_id,
            'name' => $item->product->name ?? '',
            'sku' => $item->product->sku ?? '',
            'tax_id' => $item->tax_id,
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
            'product_id' => null,
            'name' => '',
            'sku' => '',
            'tax_id' => null,
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
        $item = Product::find($itemId);
        if ($item) {
            $this->items[$index]['product_id'] = $item->id;
            $this->items[$index]['name'] = $item->name;
            $this->items[$index]['sku'] = $item->sku;
            $this->items[$index]['unit_price'] = $item->selling_price ?? 0;
            $this->items[$index]['tax_id'] = $item->sales_tax_id;
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
        $items = collect($this->items);
        $taxIds = $items->pluck('tax_id')->filter()->unique()->all();

        if (empty($taxIds)) {
            return 0.0;
        }

        $taxes = Tax::whereIn('id', $taxIds)->get()->keyBy('id');

        $totalTax = 0.0;

        foreach ($items as $item) {
            $taxId = $item['tax_id'] ?? null;
            if (! $taxId || ! isset($taxes[$taxId])) {
                continue;
            }

            $lineBase = (float) ($item['total'] ?? 0); // untaxed line total
            $tax = $taxes[$taxId];

            if ($tax->type === 'percentage') {
                $totalTax += $lineBase * ((float) $tax->rate / 100);
            } else {
                $totalTax += (float) $tax->rate;
            }
        }

        return $totalTax;
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax;
    }

    public function save(): void
    {
        // Filter out empty items (no product selected)
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();
        
        // Check if there are valid items
        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product to the order.');
            return;
        }

        // Update items with only valid ones for validation
        $this->items = $validItems;

        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'order_date.required' => 'Please enter the order date.',
            'items.required' => 'Please add at least one product to the order.',
            'items.min' => 'Please add at least one product to the order.',
            'items.*.product_id.required' => 'Please select a product for all order lines.',
            'items.*.quantity.required' => 'Please enter quantity for all products.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Please enter unit price for all products.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
        ]);

        $this->saveOrder();
    }

    public function confirm(): void
    {
        // Filter out empty items (no product selected)
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();
        
        // Check if there are valid items
        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product to the order.');
            return;
        }

        // Update items with only valid ones for validation
        $this->items = $validItems;

        // Validate first before changing status
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'order_date.required' => 'Please enter the order date.',
            'items.required' => 'Please add at least one product to the order.',
            'items.min' => 'Please add at least one product to the order.',
            'items.*.product_id.required' => 'Please select a product for all order lines.',
            'items.*.quantity.required' => 'Please enter quantity for all products.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Please enter unit price for all products.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
        ]);

        // Only change status after validation passes - set to 'sales_order'
        $this->status = SalesOrderState::SALES_ORDER->value;
        $this->saveOrder();
        
        session()->flash('success', 'Order confirmed successfully. Status changed to Sales Order.');
    }

    public function openInvoiceModal(): void
    {
        $this->invoiceType = 'regular';
        $this->downPaymentPercentage = 0;
        $this->downPaymentAmount = 0;
        $this->showInvoiceModal = true;
    }

    public function createInvoice(): void
    {
        if (!$this->orderId) {
            session()->flash('error', 'Please save the order first.');
            return;
        }

        $order = SalesOrder::with('items.product')->findOrFail($this->orderId);
        
        if (!$order->state->canCreateInvoice()) {
            session()->flash('error', 'Cannot create invoice for this order status.');
            return;
        }

        DB::beginTransaction();
        try {
            $invoiceTotal = 0;
            $invoiceSubtotal = 0;
            $invoiceTax = 0;

            // Calculate invoice amount based on type
            if ($this->invoiceType === 'regular') {
                $invoiceSubtotal = (float) $order->subtotal;
                $invoiceTax = (float) $order->tax;
                $invoiceTotal = (float) $order->total;
            } elseif ($this->invoiceType === 'down_payment_percentage') {
                $percentage = max(0, min(100, $this->downPaymentPercentage));
                $invoiceSubtotal = (float) $order->subtotal * ($percentage / 100);
                $invoiceTax = (float) $order->tax * ($percentage / 100);
                $invoiceTotal = (float) $order->total * ($percentage / 100);
            } elseif ($this->invoiceType === 'down_payment_fixed') {
                $invoiceTotal = max(0, min((float) $order->total, $this->downPaymentAmount));
                // Proportionally calculate subtotal and tax
                $ratio = $invoiceTotal / max(1, (float) $order->total);
                $invoiceSubtotal = (float) $order->subtotal * $ratio;
                $invoiceTax = (float) $order->tax * $ratio;
            }

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'subtotal' => $invoiceSubtotal,
                'tax' => $invoiceTax,
                'discount' => 0,
                'total' => $invoiceTotal,
                'notes' => $this->invoiceType !== 'regular' 
                    ? 'Down Payment for ' . $order->order_number 
                    : null,
            ]);

            // Create invoice items
            if ($this->invoiceType === 'regular') {
                foreach ($order->items as $orderItem) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $orderItem->product_id,
                        'description' => $orderItem->product->name ?? '',
                        'quantity' => $orderItem->quantity,
                        'unit_price' => $orderItem->unit_price,
                        'discount' => $orderItem->discount,
                        'total' => $orderItem->total,
                    ]);
                }
            } else {
                // For down payments, create a single line item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => null,
                    'description' => $this->invoiceType === 'down_payment_percentage'
                        ? "Down Payment ({$this->downPaymentPercentage}%) for {$order->order_number}"
                        : "Down Payment for {$order->order_number}",
                    'quantity' => 1,
                    'unit_price' => $invoiceTotal,
                    'discount' => 0,
                    'total' => $invoiceTotal,
                ]);
            }

            DB::commit();

            $this->showInvoiceModal = false;
            session()->flash('success', 'Invoice draft created successfully.');
            
            // Redirect to the invoice
            $this->redirect(route('invoicing.invoices.edit', $invoice->id), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }
    
    private function saveOrder(): void
    {
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
            $orderData['order_number'] = SalesOrder::generateOrderNumber();
            $order = SalesOrder::create($orderData);
            $this->orderId = $order->id;
        }

        foreach ($this->items as $item) {
            if ($item['product_id']) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'tax_id' => $item['tax_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                ]);
            }
        }

        session()->flash('success', 'Order saved successfully.');
        $this->redirect(route('sales.orders.edit', $order->id), navigate: true);
    }

    public function cancel(): void
    {
        if ($this->orderId) {
            $order = SalesOrder::findOrFail($this->orderId);
            $order->update(['status' => SalesOrderState::CANCELLED->value]);
            
            session()->flash('success', 'Order cancelled successfully.');
            $this->redirect(route('sales.orders.index'), navigate: true);
        }
    }

    public function getStateProperty(): SalesOrderState
    {
        return SalesOrderState::tryFrom($this->status) ?? SalesOrderState::QUOTATION;
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::query()
            ->when($this->itemSearch, fn($q) => $q->where('name', 'like', "%{$this->itemSearch}%")
                ->orWhere('sku', 'like', "%{$this->itemSearch}%"))
            ->where('status', '!=', 'out_of_stock')
            ->orderBy('name')
            ->limit(20)
            ->get();

        $selectedCustomer = $this->customer_id ? Customer::find($this->customer_id) : null;

        $taxes = Tax::query()
            ->where('is_active', true)
            ->where('scope', 'sales')
            ->orderBy('name')
            ->get();

        // Get invoices linked to this sales order
        $invoices = $this->orderId 
            ? Invoice::where('sales_order_id', $this->orderId)->get() 
            : collect();

        return view('livewire.sales.orders.form', [
            'customers' => $customers,
            'products' => $products,
            'selectedCustomer' => $selectedCustomer,
            'taxes' => $taxes,
            'invoices' => $invoices,
        ]);
    }
}
