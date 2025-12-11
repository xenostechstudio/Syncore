<?php

namespace App\Livewire\Invoicing\Invoices;

use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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
    public float $paid_amount = 0;
    public ?string $paid_date = null;

    public ?string $invoice_number = null;

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
        $invoice = Invoice::with(['customer', 'salesOrder', 'items.inventoryItem'])
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
        $this->paid_amount = (float) $invoice->paid_amount;
        $this->paid_date = $invoice->paid_date?->format('Y-m-d');
        $this->invoice_number = $invoice->invoice_number;
    }

    public function save(): void
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'status' => 'required|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
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
            'paid_amount' => $this->paid_amount,
            'paid_date' => $this->paid_date ?: null,
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

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        $salesOrders = SalesOrder::with('customer')->orderByDesc('order_date')->limit(50)->get();

        $invoice = $this->invoiceId
            ? Invoice::with(['customer', 'items.inventoryItem'])->find($this->invoiceId)
            : null;

        return view('livewire.invoicing.invoices.form', [
            'customers' => $customers,
            'salesOrders' => $salesOrders,
            'invoice' => $invoice,
        ]);
    }
}
