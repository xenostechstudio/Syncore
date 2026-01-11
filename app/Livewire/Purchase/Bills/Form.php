<?php

namespace App\Livewire\Purchase\Bills;

use App\Enums\VendorBillState;
use App\Livewire\Concerns\WithNotes;
use App\Models\Inventory\Product;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Vendor Bill')]
class Form extends Component
{
    use WithNotes;

    public ?int $billId = null;
    public ?int $supplier_id = null;
    public ?int $purchase_rfq_id = null;
    public string $vendor_reference = '';
    public string $bill_date = '';
    public string $due_date = '';
    public string $status = 'draft';
    public string $notes = '';
    public array $items = [];

    public ?string $billNumber = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    // Payment modal
    public bool $showPaymentModal = false;
    public string $paymentDate = '';
    public float $paymentAmount = 0;
    public string $paymentMethod = 'bank_transfer';
    public string $paymentReference = '';
    public string $paymentNotes = '';

    protected function getNotableModel()
    {
        return $this->billId ? VendorBill::find($this->billId) : null;
    }

    public function mount(?int $id = null): void
    {
        $this->bill_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');

        if ($id) {
            $this->billId = $id;
            $this->loadBill();
        } else {
            $this->addItem();
        }
    }

    protected function loadBill(): void
    {
        $bill = VendorBill::with(['supplier', 'items.product'])->findOrFail($this->billId);

        $this->supplier_id = $bill->supplier_id;
        $this->purchase_rfq_id = $bill->purchase_rfq_id;
        $this->vendor_reference = $bill->vendor_reference ?? '';
        $this->bill_date = $bill->bill_date->format('Y-m-d');
        $this->due_date = $bill->due_date?->format('Y-m-d') ?? '';
        $this->status = $bill->status;
        $this->notes = $bill->notes ?? '';
        $this->billNumber = $bill->bill_number;
        $this->createdAt = $bill->created_at->format('M d, Y \a\t H:i');
        $this->updatedAt = $bill->updated_at->format('M d, Y \a\t H:i');

        $this->items = $bill->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $item->product->name ?? '',
            'sku' => $item->product->sku ?? '',
            'description' => $item->description ?? '',
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'tax_amount' => $item->tax_amount,
            'total' => $item->total,
        ])->toArray();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'product_id' => null,
            'name' => '',
            'sku' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectProduct(int $index, int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->items[$index]['product_id'] = $product->id;
            $this->items[$index]['name'] = $product->name;
            $this->items[$index]['sku'] = $product->sku ?? '';
            $this->items[$index]['description'] = $product->name;
            $this->items[$index]['unit_price'] = $product->cost_price ?? 0;
            $this->calculateItemTotal($index);
        }
    }

    public function calculateItemTotal(int $index): void
    {
        $item = &$this->items[$index];
        $subtotal = $item['quantity'] * $item['unit_price'];
        $item['total'] = $subtotal + ($item['tax_amount'] ?? 0);
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $this->calculateItemTotal($index);
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
    }

    public function getTaxProperty(): float
    {
        return collect($this->items)->sum('tax_amount');
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax;
    }

    public function save(): void
    {
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();
        
        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product.');
            return;
        }

        $this->items = $validItems;

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            $data = [
                'supplier_id' => $this->supplier_id,
                'purchase_rfq_id' => $this->purchase_rfq_id ?: null,
                'vendor_reference' => $this->vendor_reference ?: null,
                'bill_date' => $this->bill_date,
                'due_date' => $this->due_date ?: null,
                'status' => $this->status,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
                'notes' => $this->notes ?: null,
                'created_by' => Auth::id(),
            ];

            if ($this->billId) {
                $bill = VendorBill::findOrFail($this->billId);
                $bill->update($data);
                $bill->items()->delete();
            } else {
                $bill = VendorBill::create($data);
                $this->billId = $bill->id;
            }

            foreach ($this->items as $item) {
                VendorBillItem::create([
                    'vendor_bill_id' => $bill->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                ]);
            }
        });

        session()->flash('success', 'Bill saved successfully.');
        $this->redirect(route('purchase.bills.edit', $this->billId), navigate: true);
    }

    public function confirm(): void
    {
        $this->status = VendorBillState::PENDING->value;
        $this->save();
    }

    public function openPaymentModal(): void
    {
        if (!$this->billId) {
            session()->flash('error', 'Please save the bill first.');
            return;
        }

        $bill = VendorBill::findOrFail($this->billId);
        $this->paymentAmount = $bill->balance_due;
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentMethod = 'bank_transfer';
        $this->paymentReference = '';
        $this->paymentNotes = '';
        $this->showPaymentModal = true;
    }

    public function registerPayment(): void
    {
        $this->validate([
            'paymentDate' => 'required|date',
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|string',
        ]);

        $bill = VendorBill::findOrFail($this->billId);

        if ($this->paymentAmount > $bill->balance_due) {
            $this->addError('paymentAmount', 'Payment amount exceeds balance due.');
            return;
        }

        DB::transaction(function () use ($bill) {
            $bill->payments()->create([
                'payment_date' => $this->paymentDate,
                'amount' => $this->paymentAmount,
                'payment_method' => $this->paymentMethod,
                'reference' => $this->paymentReference ?: null,
                'notes' => $this->paymentNotes ?: null,
                'created_by' => Auth::id(),
            ]);

            $newPaidAmount = $bill->paid_amount + $this->paymentAmount;
            $bill->paid_amount = $newPaidAmount;

            if ($newPaidAmount >= $bill->total) {
                $bill->status = VendorBillState::PAID->value;
                $bill->paid_date = $this->paymentDate;
            } else {
                $bill->status = VendorBillState::PARTIAL->value;
            }

            $bill->save();
        });

        $this->showPaymentModal = false;
        $this->loadBill();
        session()->flash('success', 'Payment registered successfully.');
    }

    public function cancel(): void
    {
        if ($this->billId) {
            $bill = VendorBill::findOrFail($this->billId);
            $bill->update(['status' => VendorBillState::CANCELLED->value]);
            
            session()->flash('success', 'Bill cancelled.');
            $this->redirect(route('purchase.bills.index'), navigate: true);
        }
    }

    public function delete(): void
    {
        if ($this->billId) {
            VendorBill::destroy($this->billId);
            session()->flash('success', 'Bill deleted.');
            $this->redirect(route('purchase.bills.index'), navigate: true);
        }
    }

    public function duplicate(): void
    {
        if (!$this->billId) {
            session()->flash('error', 'Please save the bill first.');
            return;
        }

        $bill = VendorBill::with('items')->findOrFail($this->billId);

        DB::transaction(function () use ($bill) {
            $newBill = VendorBill::create([
                'supplier_id' => $bill->supplier_id,
                'purchase_rfq_id' => null,
                'vendor_reference' => null,
                'bill_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => VendorBillState::DRAFT->value,
                'subtotal' => $bill->subtotal,
                'tax' => $bill->tax,
                'total' => $bill->total,
                'notes' => $bill->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($bill->items as $item) {
                VendorBillItem::create([
                    'vendor_bill_id' => $newBill->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_amount' => $item->tax_amount,
                    'total' => $item->total,
                ]);
            }

            session()->flash('success', 'Bill duplicated successfully.');
            $this->redirect(route('purchase.bills.edit', $newBill->id), navigate: true);
        });
    }

    public function render()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $purchaseOrders = PurchaseRfq::where('status', 'purchase_order')
            ->when($this->supplier_id, fn($q) => $q->where('supplier_id', $this->supplier_id))
            ->orderByDesc('order_date')
            ->get();

        return view('livewire.purchase.bills.form', [
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }
}
