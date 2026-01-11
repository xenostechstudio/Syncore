<?php

namespace App\Livewire\Invoicing\Payments;

use App\Livewire\Concerns\WithNotes;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Payment')]
class Form extends Component
{
    use WithNotes;

    public ?int $paymentId = null;

    public ?int $invoice_id = null;
    public string $payment_date = '';
    public float $amount = 0;
    public string $payment_method = 'bank_transfer';
    public string $reference = '';
    public string $notes = '';
    public string $status = 'completed';

    public ?string $payment_number = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    // Delete confirmation
    public bool $showDeleteConfirm = false;

    protected function getNotableModel()
    {
        return $this->paymentId ? Payment::find($this->paymentId) : null;
    }

    public function mount(?int $id = null): void
    {
        $this->payment_date = now()->format('Y-m-d');

        if ($id) {
            $this->paymentId = $id;
            $this->loadPayment();
        } else {
            $this->invoice_id = request()->integer('invoice_id') ?: null;
            
            // Pre-fill amount with remaining balance if invoice selected
            if ($this->invoice_id) {
                $invoice = Invoice::with('payments')->find($this->invoice_id);
                if ($invoice) {
                    $paidAmount = $invoice->payments->sum('amount');
                    $this->amount = max(0, (float) $invoice->total - $paidAmount);
                }
            }
        }
    }

    protected function loadPayment(): void
    {
        $payment = Payment::with(['invoice.customer'])->findOrFail($this->paymentId);

        $this->invoice_id = $payment->invoice_id;
        $this->payment_date = $payment->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->amount = (float) $payment->amount;
        $this->payment_method = $payment->payment_method ?? 'bank_transfer';
        $this->reference = $payment->reference ?? '';
        $this->notes = $payment->notes ?? '';
        $this->status = $payment->status ?? 'completed';
        $this->payment_number = $payment->payment_number;
        $this->createdAt = $payment->created_at?->format('M d, Y \a\t H:i');
        $this->updatedAt = $payment->updated_at?->format('M d, Y \a\t H:i');
    }

    protected function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|string|max:50',
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Validate amount doesn't exceed remaining
        $invoice = Invoice::with('payments')->findOrFail($this->invoice_id);
        $paidAmount = $invoice->payments->where('id', '!=', $this->paymentId)->sum('amount');
        $remaining = $invoice->total - $paidAmount;

        if ($this->amount > $remaining + 0.01) {
            $this->addError('amount', 'Payment amount cannot exceed remaining balance of Rp ' . number_format($remaining, 0, ',', '.'));
            return;
        }

        DB::transaction(function () use ($invoice, $paidAmount) {
            $data = [
                'invoice_id' => $this->invoice_id,
                'payment_date' => $this->payment_date,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'reference' => $this->reference ?: null,
                'notes' => $this->notes ?: null,
                'status' => $this->status,
            ];

            if ($this->paymentId) {
                $payment = Payment::findOrFail($this->paymentId);
                $payment->update($data);
                session()->flash('success', 'Payment updated successfully.');
            } else {
                // Generate payment number
                $data['payment_number'] = $this->generatePaymentNumber();
                $payment = Payment::create($data);
                $this->paymentId = $payment->id;
                $this->payment_number = $payment->payment_number;
                session()->flash('success', 'Payment created successfully.');
            }

            // Update invoice status
            $newPaidAmount = $paidAmount + $this->amount;
            if ($newPaidAmount >= $invoice->total) {
                $invoice->update(['status' => 'paid', 'paid_amount' => $newPaidAmount, 'paid_date' => now()]);
            } elseif ($newPaidAmount > 0) {
                $invoice->update(['status' => 'partial', 'paid_amount' => $newPaidAmount]);
            }

            $this->createdAt = $payment->created_at?->format('M d, Y \a\t H:i');
            $this->updatedAt = $payment->updated_at?->format('M d, Y \a\t H:i');
        });

        if (!$this->paymentId) {
            return;
        }

        // Redirect to edit if just created
        if (!request()->routeIs('invoicing.payments.edit')) {
            $this->redirect(route('invoicing.payments.edit', $this->paymentId), navigate: true);
        }
    }

    protected function generatePaymentNumber(): string
    {
        $year = now()->year;
        $prefix = "PAY/{$year}/";

        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(payment_number, " . (strlen($prefix) + 1) . ") AS INTEGER) DESC")
            ->first();

        $nextNumber = $lastPayment 
            ? ((int) substr($lastPayment->payment_number, strlen($prefix))) + 1 
            : 1;

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function confirmDelete(): void
    {
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
    }

    public function delete(): void
    {
        if (!$this->paymentId) {
            return;
        }

        $payment = Payment::findOrFail($this->paymentId);
        $invoice = Invoice::with('payments')->find($payment->invoice_id);

        DB::transaction(function () use ($payment, $invoice) {
            $payment->delete();

            // Recalculate invoice status
            if ($invoice) {
                $newPaidAmount = $invoice->payments->where('id', '!=', $payment->id)->sum('amount');
                
                if ($newPaidAmount <= 0) {
                    $invoice->update(['status' => 'sent', 'paid_amount' => 0, 'paid_date' => null]);
                } elseif ($newPaidAmount < $invoice->total) {
                    $invoice->update(['status' => 'partial', 'paid_amount' => $newPaidAmount]);
                }
            }
        });

        session()->flash('success', 'Payment deleted successfully.');
        $this->redirect(route('invoicing.payments.index'), navigate: true);
    }

    public function render()
    {
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->orderByDesc('invoice_date')
            ->get();

        $payment = $this->paymentId 
            ? Payment::with(['invoice.customer'])->find($this->paymentId) 
            : null;

        $selectedInvoice = $this->invoice_id 
            ? Invoice::with(['customer', 'payments'])->find($this->invoice_id) 
            : null;

        $invoiceRemaining = 0;
        if ($selectedInvoice) {
            $paidAmount = $selectedInvoice->payments->where('id', '!=', $this->paymentId)->sum('amount');
            $invoiceRemaining = max(0, $selectedInvoice->total - $paidAmount);
        }

        return view('livewire.invoicing.payments.form', [
            'invoices' => $invoices,
            'payment' => $payment,
            'selectedInvoice' => $selectedInvoice,
            'invoiceRemaining' => $invoiceRemaining,
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
