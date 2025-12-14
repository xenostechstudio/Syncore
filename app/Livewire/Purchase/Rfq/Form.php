<?php

namespace App\Livewire\Purchase\Rfq;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('RFQ')]
class Form extends Component
{
    public ?int $rfqId = null;
    public string $reference = '';
    public ?int $supplier_id = null;
    public string $supplier_name = '';
    public string $supplier_reference = '';
    public string $order_date = '';
    public string $expected_arrival = '';
    public string $deliver_to = '';
    public string $status = 'rfq';
    public array $lines = [];
    public float $subtotal = 0;
    public float $tax = 0;
    public float $total = 0;

    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    public array $activityLog = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $rfq = DB::table('purchase_rfqs')->where('id', $id)->first();
            
            if ($rfq) {
                $this->rfqId = $rfq->id;
                $this->reference = $rfq->reference;
                $this->supplier_id = $rfq->supplier_id ? (int) $rfq->supplier_id : null;
                $this->supplier_name = $rfq->supplier_name;
                $this->order_date = $rfq->order_date;
                $this->expected_arrival = $rfq->expected_arrival ?? '';
                $this->status = $rfq->status;
                $this->subtotal = $rfq->subtotal;
                $this->tax = $rfq->tax;
                $this->total = $rfq->total;

                $this->createdAt = \Carbon\Carbon::parse($rfq->created_at)->format('M d, Y \a\t H:i');
                $this->updatedAt = \Carbon\Carbon::parse($rfq->updated_at)->format('M d, Y \a\t H:i');

                $this->activityLog = [
                    [
                        'type' => 'created',
                        'message' => 'RFQ created',
                        'user' => Auth::user()?->name ?? 'System',
                        'time' => $this->createdAt,
                    ],
                ];

                if ($rfq->updated_at > $rfq->created_at) {
                    $this->activityLog[] = [
                        'type' => 'updated',
                        'message' => 'RFQ updated',
                        'user' => Auth::user()?->name ?? 'System',
                        'time' => $this->updatedAt,
                    ];
                }
            }
        } else {
            $this->order_date = now()->format('Y-m-d');
            $this->reference = 'RFQ-' . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT);
            $this->addLine();
        }

        $this->recalculateTotals();
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'id' => null,
            'product_id' => null,
            'product_name' => '',
            'product_sku' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount' => 0,
            'total' => 0,
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->recalculateTotals();
    }

    public function updatedLines($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $this->calculateLineTotal($index);
        }
    }

    public function calculateLineTotal(int $index): void
    {
        if (! isset($this->lines[$index])) {
            return;
        }

        $line = &$this->lines[$index];
        $qty = (float) ($line['quantity'] ?? 0);
        $price = (float) ($line['unit_price'] ?? 0);
        $discount = (float) ($line['discount'] ?? 0);

        $lineTotal = ($qty * $price) - $discount;
        $line['total'] = max(0, $lineTotal);

        $this->recalculateTotals();
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = (float) collect($this->lines)->sum(fn ($l) => (float) ($l['total'] ?? 0));
        $this->tax = 0;
        $this->total = $this->subtotal + $this->tax;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
        ], [
            'supplier_id.required' => 'Please select a supplier.',
            'supplier_id.exists' => 'Selected supplier is invalid.',
        ]);

        $this->recalculateTotals();

        if ($this->rfqId) {
            DB::table('purchase_rfqs')
                ->where('id', $this->rfqId)
                ->update([
                    'supplier_id' => $this->supplier_id,
                    'supplier_name' => $this->supplier_name,
                    'order_date' => $this->order_date,
                    'expected_arrival' => $this->expected_arrival,
                    'status' => $this->status,
                    'subtotal' => $this->subtotal,
                    'tax' => $this->tax,
                    'total' => $this->total,
                    'updated_at' => now(),
                ]);

            session()->flash('success', 'RFQ updated successfully.');
        } else {
            $id = DB::table('purchase_rfqs')->insertGetId([
                'reference' => $this->reference,
                'supplier_id' => $this->supplier_id,
                'supplier_name' => $this->supplier_name,
                'order_date' => $this->order_date,
                'expected_arrival' => $this->expected_arrival,
                'status' => $this->status,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('success', 'RFQ created successfully.');
            $this->redirect(route('purchase.rfq.edit', $id), navigate: true);
        }
    }

    public function sendRfq(): void
    {
        if (!$this->rfqId) {
            session()->flash('error', 'Please save the RFQ first.');
            return;
        }

        DB::table('purchase_rfqs')
            ->where('id', $this->rfqId)
            ->update([
                'status' => 'sent',
                'updated_at' => now(),
            ]);

        $this->status = 'sent';
        session()->flash('success', 'RFQ sent successfully.');
    }

    public function confirmOrder(): void
    {
        if (!$this->rfqId) {
            session()->flash('error', 'Please save the RFQ first.');
            return;
        }

        DB::table('purchase_rfqs')
            ->where('id', $this->rfqId)
            ->update([
                'status' => 'purchase_order',
                'updated_at' => now(),
            ]);

        session()->flash('success', 'RFQ confirmed as Purchase Order.');
        $this->redirect(route('purchase.orders.edit', $this->rfqId), navigate: true);
    }

    public function cancel(): void
    {
        if (!$this->rfqId) {
            return;
        }

        DB::table('purchase_rfqs')
            ->where('id', $this->rfqId)
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

        session()->flash('success', 'RFQ cancelled.');
        $this->redirect(route('purchase.rfq.index'), navigate: true);
    }

    public function delete(): void
    {
        if (!$this->rfqId) {
            return;
        }

        DB::table('purchase_rfqs')->where('id', $this->rfqId)->delete();

        session()->flash('success', 'RFQ deleted successfully.');
        $this->redirect(route('purchase.rfq.index'), navigate: true);
    }

    public function selectProduct(int $index, int $productId): void
    {
        if (! isset($this->lines[$index])) {
            return;
        }

        $product = DB::table('products')->where('id', $productId)->first();

        if (! $product) {
            return;
        }

        $this->lines[$index]['product_id'] = $product->id;
        $this->lines[$index]['product_name'] = $product->name;
        $this->lines[$index]['product_sku'] = $product->sku ?? '';
        $this->lines[$index]['description'] = $product->description ?? '';
        $this->lines[$index]['unit_price'] = $product->cost_price ?? 0;

        $this->calculateLineTotal($index);
    }

    public function selectSupplier(int $supplierId): void
    {
        $supplier = DB::table('suppliers')->where('id', $supplierId)->first();

        if ($supplier) {
            $this->supplier_id = $supplier->id;
            $this->supplier_name = $supplier->name;
        }
    }

    protected function getViewData(): array
    {
        $suppliers = DB::table('suppliers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedSupplier = null;

        if ($this->supplier_id) {
            $selectedSupplier = $suppliers->firstWhere('id', $this->supplier_id)
                ?? DB::table('suppliers')->where('id', $this->supplier_id)->first();
        }

        if (! $selectedSupplier && $this->supplier_name) {
            $selectedSupplier = (object) [
                'name' => $this->supplier_name,
                'email' => null,
                'contact_person' => null,
            ];
        }

        return [
            'suppliers' => $suppliers,
            'selectedSupplier' => $selectedSupplier,
            'products' => DB::table('products')
                ->select('id', 'name', 'sku', 'description', 'cost_price')
                ->orderBy('name')
                ->limit(50)
                ->get(),
        ];
    }

    public function render()
    {
        return view('livewire.purchase.rfq.form', $this->getViewData());
    }
}
