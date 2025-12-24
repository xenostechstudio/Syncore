<?php

namespace App\Livewire\Invoicing\Invoices;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Invoicing\Payment;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Services\XenditService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Invoice')]
class Form extends Component
{
    public ?int $invoiceId = null;

    public ?int $customer_id = null;
    public ?int $sales_order_id = null;
    public string $invoice_date = '';
    public ?string $due_date = null;
    public string $status = 'draft';
    public string $notes = '';
    public string $terms = '';
    public float $subtotal = 0;
    public float $tax = 0;
    public float $discount = 0;
    public float $total = 0;

    public ?string $invoice_number = null;
    
    // Payment modal
    public bool $showPaymentModal = false;
    public string $paymentType = 'manual';
    public float $paymentAmount = 0;
    public string $paymentDate = '';
    public string $paymentMethod = 'bank_transfer';
    public string $paymentReference = '';
    public bool $showShareModal = false;
    public ?string $shareLink = null;

    public function getActivities(): \Illuminate\Support\Collection
    {
        if (!$this->invoiceId) {
            return collect();
        }

        return Activity::where('subject_type', Invoice::class)
            ->where('subject_id', $this->invoiceId)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();
    }

    public function mount(?int $id = null): void
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');

        if ($id) {
            $this->invoiceId = $id;
            $this->loadInvoice();
        } else {
            $this->customer_id = request()->integer('customer_id') ?: null;
            $this->sales_order_id = request()->integer('sales_order_id') ?: null;
        }
    }

    protected function loadInvoice(): void
    {
        $invoice = Invoice::with(['customer', 'salesOrder', 'items.product', 'items.tax', 'payments'])
            ->findOrFail($this->invoiceId);

        $this->customer_id = $invoice->customer_id;
        $this->sales_order_id = $invoice->sales_order_id;
        $this->invoice_date = $invoice->invoice_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->due_date = $invoice->due_date?->format('Y-m-d');
        $this->status = $invoice->status;
        $this->notes = $invoice->notes ?? '';
        $this->terms = $invoice->terms ?? '';
        $this->subtotal = (float) $invoice->subtotal;
        $this->tax = (float) $invoice->tax;
        $this->discount = (float) $invoice->discount;
        $this->total = (float) $invoice->total;
        $this->invoice_number = $invoice->invoice_number;
    }

    public function save(): void
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'status' => 'required|string|max:50',
        ]);

        $data = [
            'customer_id' => $this->customer_id,
            'sales_order_id' => $this->sales_order_id,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
            'terms' => $this->terms ?: null,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
        ];

        if ($this->invoiceId) {
            $invoice = Invoice::findOrFail($this->invoiceId);
            $invoice->update($data);
            $this->invoice_number = $invoice->invoice_number;
            session()->flash('success', 'Invoice updated successfully.');
        } else {
            $invoice = Invoice::create($data);
            $this->invoiceId = $invoice->id;
            $this->invoice_number = $invoice->invoice_number;
            session()->flash('success', 'Invoice created successfully.');

            $this->redirect(route('invoicing.invoices.edit', $invoice->id), navigate: true);
            return;
        }
    }

    public function delete(): void
    {
        if ($this->invoiceId) {
            Invoice::destroy($this->invoiceId);
            session()->flash('success', 'Invoice deleted successfully.');
            $this->redirect(route('invoicing.invoices.index'), navigate: true);
        }
    }

    public function cancel(): void
    {
        if (!$this->invoiceId) {
            return;
        }

        $invoice = Invoice::with('items')->findOrFail($this->invoiceId);

        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            session()->flash('error', 'This invoice cannot be cancelled.');
            return;
        }

        DB::transaction(function () use ($invoice) {
            // Decrement quantity_invoiced on sales order items
            if ($invoice->sales_order_id) {
                foreach ($invoice->items as $invoiceItem) {
                    if ($invoiceItem->product_id) {
                        SalesOrderItem::query()
                            ->where('sales_order_id', $invoice->sales_order_id)
                            ->where('product_id', $invoiceItem->product_id)
                            ->decrement('quantity_invoiced', $invoiceItem->quantity);
                    }
                }
            }

            $invoice->update([
                'status' => 'cancelled',
                'xendit_status' => $invoice->xendit_status ?? 'cancelled',
            ]);
        });

        $this->status = 'cancelled';
        session()->flash('success', 'Invoice cancelled successfully.');
    }

    public function openPaymentModal(): void
    {
        $remainingAmount = 0;
        if ($this->invoiceId) {
            $invoice = Invoice::with('payments')->find($this->invoiceId);
            if ($invoice) {
                $remainingAmount = max(0, (float) $invoice->total - (float) $invoice->payments->sum('amount'));
            }
        }

        $this->paymentType = 'manual';
        $this->paymentAmount = $remainingAmount > 0 ? $remainingAmount : 0;
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentMethod = 'bank_transfer';
        $this->paymentReference = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function addPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|string',
        ]);

        $invoice = Invoice::with('payments')->findOrFail($this->invoiceId);
        $paidAmount = $invoice->payments->sum('amount');
        $remaining = $invoice->total - $paidAmount;

        if ($this->paymentAmount > $remaining) {
            $this->addError('paymentAmount', 'Payment amount cannot exceed remaining amount.');
            return;
        }

        // Generate payment number
        $year = now()->year;
        $prefix = "PAY/{$year}/";

        $castType = match (DB::connection()->getDriverName()) {
            'pgsql', 'sqlite' => 'INTEGER',
            default => 'UNSIGNED',
        };

        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(payment_number, LENGTH(?) + 1) AS {$castType}) DESC", [$prefix])
            ->first();
        $nextNumber = $lastPayment ? ((int) substr($lastPayment->payment_number, strlen($prefix))) + 1 : 1;
        $paymentNumber = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        Payment::create([
            'payment_number' => $paymentNumber,
            'invoice_id' => $this->invoiceId,
            'amount' => $this->paymentAmount,
            'payment_date' => $this->paymentDate,
            'payment_method' => $this->paymentMethod,
            'reference' => $this->paymentReference ?: null,
            'status' => 'completed',
        ]);

        // Update invoice status based on payment
        $newPaidAmount = $paidAmount + $this->paymentAmount;
        if ($newPaidAmount >= $invoice->total) {
            $invoice->update(['status' => 'paid', 'paid_amount' => $newPaidAmount]);
            $this->status = 'paid';
        } else {
            $invoice->update(['status' => 'partial', 'paid_amount' => $newPaidAmount]);
            $this->status = 'partial';
        }

        $this->showPaymentModal = false;
        session()->flash('success', 'Payment recorded successfully.');
    }

    public function createXenditPayment(bool $forceNew = false): void
    {
        if (!$this->invoiceId) {
            session()->flash('error', 'Please save the invoice first.');
            return;
        }

        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($this->invoiceId);

        if ($this->status === 'paid') {
            session()->flash('error', 'This invoice is already paid.');
            return;
        }

        $xenditService = app(XenditService::class);

        if (!$xenditService->isConfigured()) {
            session()->flash('error', 'Xendit is not configured. Please add your API keys in Settings > Payment Gateway.');
            return;
        }

        if (!$forceNew && !empty($invoice->xendit_invoice_id) && !empty($invoice->xendit_invoice_url)) {
            $status = strtolower((string) ($invoice->xendit_status ?? ''));
            if (!in_array($status, ['paid', 'expired'], true)) {
                session()->flash('success', 'Using existing pending Xendit payment.');
                return;
            }
        }

        $result = $xenditService->createInvoice($invoice);

        if ($result['success']) {
            session()->flash('success', 'Xendit payment link created successfully.');
            $this->showPaymentModal = true;
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function getXenditConfiguredProperty(): bool
    {
        return app(XenditService::class)->isConfigured();
    }

    public function openShareModal(): void
    {
        if (!$this->invoiceId) {
            session()->flash('error', 'Please save the invoice first.');
            return;
        }

        $invoice = Invoice::findOrFail($this->invoiceId);
        $invoice->ensureShareToken();

        $this->shareLink = URL::signedRoute('public.invoices.show', [
            'token' => $invoice->share_token,
        ]);

        $this->showShareModal = true;
    }

    public function regenerateShareLink(): void
    {
        if (!$this->invoiceId) {
            return;
        }

        $invoice = Invoice::findOrFail($this->invoiceId);
        $invoice->ensureShareToken(forceRefresh: true);

        $this->shareLink = URL::signedRoute('public.invoices.show', [
            'token' => $invoice->share_token,
        ]);
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        $salesOrders = SalesOrder::with('customer')->orderByDesc('order_date')->limit(50)->get();

        $invoice = $this->invoiceId
            ? Invoice::with(['customer', 'items.product', 'items.tax', 'payments', 'salesOrder'])->find($this->invoiceId)
            : null;

        $paidAmount = $invoice ? $invoice->payments->sum('amount') : 0;
        $remainingAmount = $invoice ? $invoice->total - $paidAmount : 0;

        return view('livewire.invoicing.invoices.form', [
            'customers' => $customers,
            'salesOrders' => $salesOrders,
            'invoice' => $invoice,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'activities' => $this->getActivities(),
        ]);
    }
}
