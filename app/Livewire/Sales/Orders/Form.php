<?php

namespace App\Livewire\Sales\Orders;

use App\Enums\DeliveryOrderState;
use App\Enums\SalesOrderState;
use App\Livewire\Concerns\WithNotes;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Sales\Tax;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL as UrlFacade;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Order')]
class Form extends Component
{
    use WithNotes;

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
    public bool $showDeliveryModal = false;
    public string $itemSearch = '';

    // Invoice Creation Options
    public string $invoiceType = 'regular'; // regular, down_payment_percentage, down_payment_fixed
    public float $downPaymentPercentage = 0;
    public float $downPaymentAmount = 0;

    // Delivery Creation Options
    public ?int $deliveryWarehouseId = null;
    public string $deliveryDate = '';
    public string $deliveryRecipientName = '';
    public string $deliveryRecipientPhone = '';
    public string $deliveryCourier = '';
    public string $deliveryTrackingNumber = '';
    public string $deliveryNotes = '';

    // History/Activity Log
    public array $activityLog = [];

    // Preview Link
    public ?string $previewLink = null;

    // Email Modal
    public bool $showEmailModal = false;
    public array $emailRecipients = [];
    public string $emailRecipientInput = '';
    public string $emailRecipientError = '';
    public string $emailSubject = '';
    public string $emailBody = '';
    public bool $emailAttachPdf = true;

    protected function getNotableModel()
    {
        return $this->orderId ? SalesOrder::find($this->orderId) : null;
    }

    public function getActivities(): \Illuminate\Support\Collection
    {
        if (!$this->orderId) {
            return collect();
        }

        $modelClass = SalesOrder::class;

        return DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.model_type', $modelClass)
            ->where('activity_logs.model_id', $this->orderId)
            ->select('activity_logs.*', 'users.name as causer_name')
            ->orderByDesc('activity_logs.created_at')
            ->limit(20)
            ->get()
            ->map(fn($activity) => (object) [
                'id' => $activity->id,
                'action' => $activity->action,
                'description' => $activity->description,
                'properties' => json_decode($activity->properties ?? '{}', true),
                'causer' => (object) ['name' => $activity->causer_name ?? $activity->user_name ?? 'System'],
                'created_at' => \Carbon\Carbon::parse($activity->created_at),
            ]);
    }

    public function mount(?int $id = null): void
    {
        $this->order_date = now()->format('Y-m-d');
        $this->expected_delivery_date = now()->addDays(7)->format('Y-m-d');
        $this->deliveryDate = now()->format('Y-m-d');

        if ($id) {
            $this->orderId = $id;
            $this->loadOrder();
        } else {
            // Add one empty item row
            $this->addItem();
        }
    }

    public function openDeliveryModal(): void
    {
        if (!$this->orderId) {
            session()->flash('error', 'Please save/confirm the sales order first.');
            return;
        }

        if ($this->status !== SalesOrderState::SALES_ORDER->value) {
            session()->flash('error', 'Delivery order can only be created for confirmed sales orders.');
            return;
        }

        $order = SalesOrder::with(['customer'])->findOrFail($this->orderId);

        $this->deliveryWarehouseId = Warehouse::query()->orderBy('name')->value('id');
        $this->deliveryDate = now()->format('Y-m-d');
        $this->deliveryRecipientName = $order->customer->name ?? '';
        $this->deliveryRecipientPhone = $order->customer->phone ?? '';
        $this->deliveryCourier = '';
        $this->deliveryTrackingNumber = '';
        $this->deliveryNotes = '';

        $this->showDeliveryModal = true;
    }

    public function closeDeliveryModal(): void
    {
        $this->showDeliveryModal = false;
    }

    public function createDeliveryOrder(): void
    {
        if (!$this->orderId) {
            session()->flash('error', 'Sales order not found.');
            return;
        }

        if ($this->status !== SalesOrderState::SALES_ORDER->value) {
            session()->flash('error', 'Delivery order can only be created for confirmed sales orders.');
            return;
        }

        // Check if there's already an active (non-cancelled) delivery order
        $hasActiveDelivery = DeliveryOrder::where('sales_order_id', $this->orderId)
            ->where('status', '!=', DeliveryOrderState::CANCELLED->value)
            ->exists();

        if ($hasActiveDelivery) {
            session()->flash('error', 'An active delivery order already exists for this sales order.');
            return;
        }

        $this->validate([
            'deliveryWarehouseId' => 'required|integer',
            'deliveryDate' => 'required|date',
            'deliveryRecipientName' => 'nullable|string|max:255',
            'deliveryRecipientPhone' => 'nullable|string|max:255',
            'deliveryCourier' => 'nullable|string|max:255',
            'deliveryTrackingNumber' => 'nullable|string|max:255',
            'deliveryNotes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = SalesOrder::with(['customer', 'items'])->findOrFail($this->orderId);

            $delivery = DeliveryOrder::create([
                'delivery_number' => DeliveryOrder::generateDeliveryNumber(),
                'sales_order_id' => $order->id,
                'warehouse_id' => $this->deliveryWarehouseId,
                'user_id' => Auth::id(),
                'delivery_date' => $this->deliveryDate,
                'status' => 'pending',
                'shipping_address' => $order->shipping_address ?? ($order->customer->address ?? ''),
                'recipient_name' => $this->deliveryRecipientName ?: ($order->customer->name ?? null),
                'recipient_phone' => $this->deliveryRecipientPhone ?: ($order->customer->phone ?? null),
                'notes' => $this->deliveryNotes ?: null,
                'tracking_number' => $this->deliveryTrackingNumber ?: null,
                'courier' => $this->deliveryCourier ?: null,
            ]);

            // Create DO items for all SO items that haven't been fully delivered yet
            foreach ($order->items as $item) {
                $qtyToDeliver = $item->quantity_to_deliver;
                if ($qtyToDeliver <= 0) {
                    continue;
                }

                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'sales_order_item_id' => $item->id,
                    'quantity_to_deliver' => $qtyToDeliver,
                    'quantity_delivered' => 0,
                ]);
            }

            DB::commit();

            $this->showDeliveryModal = false;
            session()->flash('success', 'Delivery order created successfully.');
            $this->redirect(route('delivery.orders.edit', $delivery->id), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to create delivery order: ' . $e->getMessage());
        }
    }

    public function loadOrder(): void
    {
        $order = SalesOrder::with(['customer', 'items.product', 'items.tax'])->findOrFail($this->orderId);

        $this->customer_id = $order->customer_id;
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->expected_delivery_date = $order->expected_delivery_date?->format('Y-m-d') ?? '';
        $this->status = $order->status;
        $this->payment_terms = $order->payment_terms ?? '';
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

        // Activity log is now handled by Spatie Activity Log via getActivities()
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

    /**
     * Get validation rules for the order
     */
    protected function orderRules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get validation messages for the order
     */
    protected function orderMessages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'order_date.required' => 'Please enter the order date.',
            'items.required' => 'Please add at least one product to the order.',
            'items.min' => 'Please add at least one product to the order.',
            'items.*.product_id.required' => 'Please select a product for all order lines.',
            'items.*.quantity.required' => 'Please enter quantity for all products.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Please enter unit price for all products.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
        ];
    }

    /**
     * Validate and filter order items
     */
    protected function validateOrderItems(): bool
    {
        // Filter out empty items (no product selected)
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();
        
        // Check if there are valid items
        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product to the order.');
            return false;
        }

        // Update items with only valid ones for validation
        $this->items = $validItems;
        return true;
    }

    public function save(): void
    {
        if (!$this->validateOrderItems()) {
            return;
        }

        $this->validate($this->orderRules(), $this->orderMessages());
        $this->saveOrder();
    }

    public function confirm(): void
    {
        if (!$this->validateOrderItems()) {
            return;
        }

        $this->validate($this->orderRules(), $this->orderMessages());

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

        // Check if there are items to invoice
        if (!$order->hasQuantityToInvoice()) {
            session()->flash('error', 'All items have already been invoiced.');
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

            // Create invoice items and update quantity_invoiced
            if ($this->invoiceType === 'regular') {
                foreach ($order->items as $orderItem) {
                    $qtyToInvoice = $orderItem->quantity_to_invoice;
                    if ($qtyToInvoice <= 0) {
                        continue;
                    }

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $orderItem->product_id,
                        'tax_id' => $orderItem->tax_id,
                        'description' => $orderItem->product->name ?? '',
                        'quantity' => $qtyToInvoice,
                        'unit_price' => $orderItem->unit_price,
                        'discount' => $orderItem->discount,
                        'total' => $qtyToInvoice * $orderItem->unit_price - $orderItem->discount,
                    ]);

                    // Update quantity_invoiced on sales order item
                    $orderItem->increment('quantity_invoiced', $qtyToInvoice);
                }
            } else {
                // For down payments, create a single line item (no quantity tracking)
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
            'payment_terms' => $this->payment_terms ?: null,
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
            
            // Only update items if order is not locked (has no active invoices/deliveries)
            if (!$order->isLocked()) {
                $order->items()->delete();
                
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
            }
        } else {
            $orderData['order_number'] = SalesOrder::generateOrderNumber();
            $order = SalesOrder::create($orderData);
            $this->orderId = $order->id;

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

    public function generatePreviewLink(): void
    {
        if (! $this->orderId) {
            session()->flash('error', 'Please save the order first.');
            return;
        }

        $order = SalesOrder::findOrFail($this->orderId);
        $order->ensureShareToken();

        $this->previewLink = UrlFacade::signedRoute('public.sales-orders.show', [
            'token' => $order->share_token,
        ]);
    }

    public function refreshPreviewLink(): void
    {
        if (! $this->orderId) {
            return;
        }

        $order = SalesOrder::findOrFail($this->orderId);
        $order->ensureShareToken(forceRefresh: true);

        $this->previewLink = UrlFacade::signedRoute('public.sales-orders.show', [
            'token' => $order->share_token,
        ]);
    }

    public function openEmailModal(): void
    {
        if (! $this->orderId) {
            session()->flash('error', 'Please save the order first.');
            return;
        }

        $order = SalesOrder::with(['customer', 'user'])->findOrFail($this->orderId);
        $order->ensureShareToken();

        // Pre-fill email fields
        $this->emailRecipients = [];
        $this->emailRecipientInput = '';
        $this->emailRecipientError = '';
        
        if ($order->customer->email) {
            $this->emailRecipients[] = $order->customer->email;
        }
        
        $isQuotation = in_array($order->status, ['draft', 'confirmed']);
        $documentType = $isQuotation ? 'Quotation' : 'Sales Order';
        
        $this->emailSubject = "{$documentType} {$order->order_number} from " . config('app.name');
        
        // Generate preview link for the email body
        $previewUrl = UrlFacade::signedRoute('public.sales-orders.show', [
            'token' => $order->share_token,
        ]);
        
        $salesperson = $order->user;
        $salespersonName = $salesperson?->name ?? 'Our Team';
        
        $this->emailBody = $this->getDefaultEmailBody($order, $documentType, $previewUrl, $salespersonName);
        $this->emailAttachPdf = true;
        $this->showEmailModal = true;
    }

    public function addEmailRecipient(): void
    {
        $email = trim($this->emailRecipientInput);
        $this->emailRecipientError = '';
        
        if (empty($email)) {
            return;
        }
        
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->emailRecipientError = 'Please enter a valid email address.';
            return;
        }
        
        if (in_array($email, $this->emailRecipients)) {
            $this->emailRecipientError = 'This email is already added.';
            return;
        }
        
        $this->emailRecipients[] = $email;
        $this->emailRecipientInput = '';
    }

    public function removeEmailRecipient(int $index): void
    {
        if (isset($this->emailRecipients[$index])) {
            unset($this->emailRecipients[$index]);
            $this->emailRecipients = array_values($this->emailRecipients);
        }
    }

    private function getDefaultEmailBody(SalesOrder $order, string $documentType, string $previewUrl, string $salespersonName): string
    {
        $customerName = $order->customer->name ?? 'Valued Customer';
        $total = 'Rp ' . number_format($order->total, 0, ',', '.');
        
        return "Dear {$customerName},

Please find attached your {$documentType} {$order->order_number} amounting to {$total}.

You can view and confirm this {$documentType} online by clicking the link below:
{$previewUrl}

If you have any questions, please don't hesitate to contact us.

Best regards,
{$salespersonName}";
    }

    public function sendEmail(): void
    {
        if (! $this->orderId) {
            session()->flash('error', 'Order not found.');
            return;
        }

        if (empty($this->emailRecipients)) {
            $this->emailRecipientError = 'Please add at least one recipient.';
            return;
        }

        $this->validate([
            'emailSubject' => 'required|string|max:255',
            'emailBody' => 'required|string',
        ], [
            'emailSubject.required' => 'Please enter email subject.',
            'emailBody.required' => 'Please enter email body.',
        ]);

        try {
            $order = SalesOrder::with(['customer', 'items.product'])->findOrFail($this->orderId);
            
            // Send the email
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($order) {
                $message->to($this->emailRecipients)
                    ->subject($this->emailSubject)
                    ->html(nl2br(e($this->emailBody)));
                
                if ($this->emailAttachPdf) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sales-order', [
                        'salesOrder' => $order,
                    ]);
                    $isQuotation = in_array($order->status, ['draft', 'confirmed']);
                    $documentType = $isQuotation ? 'Quotation' : 'Sales Order';
                    $message->attachData(
                        $pdf->output(),
                        "{$documentType} - {$order->order_number}.pdf",
                        ['mime' => 'application/pdf']
                    );
                }
            });

            // Update status to quotation_sent if it was draft
            if ($order->status === 'draft') {
                $order->update(['status' => 'confirmed']);
            }

            $this->showEmailModal = false;
            $recipientCount = count($this->emailRecipients);
            session()->flash('success', "Email sent successfully to {$recipientCount} recipient(s).");
            
            // Reload order to reflect status change
            $this->loadOrder();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function getStateProperty(): SalesOrderState
    {
        return SalesOrderState::tryFrom($this->status) ?? SalesOrderState::QUOTATION;
    }

    public function duplicate(): void
    {
        if (!$this->orderId) {
            session()->flash('error', 'Please save the order first.');
            return;
        }

        try {
            $order = SalesOrder::with('items')->findOrFail($this->orderId);

            // Create new order with copied data
            $newOrder = SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'customer_id' => $order->customer_id,
                'user_id' => Auth::id(),
                'order_date' => now(),
                'expected_delivery_date' => now()->addDays(7),
                'status' => 'draft',
                'payment_terms' => $order->payment_terms,
                'notes' => $order->notes,
                'terms' => $order->terms,
                'shipping_address' => $order->shipping_address,
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'discount' => $order->discount,
                'total' => $order->total,
            ]);

            // Copy items
            foreach ($order->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $newOrder->id,
                    'product_id' => $item->product_id,
                    'tax_id' => $item->tax_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'total' => $item->total,
                ]);
            }

            session()->flash('success', 'Order duplicated successfully.');
            $this->redirect(route('sales.orders.edit', $newOrder->id), navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to duplicate order: ' . $e->getMessage());
        }
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

        $warehouses = Warehouse::query()->orderBy('name')->get();

        // Get invoices linked to this sales order
        $invoices = $this->orderId 
            ? Invoice::where('sales_order_id', $this->orderId)
                ->where('status', '!=', 'cancelled')
                ->get() 
            : collect();

        $deliveries = $this->orderId
            ? DeliveryOrder::where('sales_order_id', $this->orderId)
                ->where('status', '!=', DeliveryOrderState::CANCELLED->value)
                ->get()
            : collect();

        // Get the sales order with items for quantity tracking
        $order = $this->orderId
            ? SalesOrder::with('items')->find($this->orderId)
            : null;

        return view('livewire.sales.orders.form', [
            'customers' => $customers,
            'products' => $products,
            'selectedCustomer' => $selectedCustomer,
            'taxes' => $taxes,
            'warehouses' => $warehouses,
            'invoices' => $invoices,
            'deliveries' => $deliveries,
            'order' => $order,
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
