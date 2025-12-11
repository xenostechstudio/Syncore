<?php

namespace App\Livewire\Inventory\Adjustments;

use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryAdjustmentItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Adjustment')]
class Form extends Component
{
    public ?int $adjustmentId = null;
    public ?InventoryAdjustment $adjustment = null;
    public bool $editing = false;

    public ?int $warehouse_id = null;
    public string $adjustment_date = '';
    public string $adjustment_type = 'count';
    public string $status = 'draft';
    public string $reason = '';
    public string $notes = '';

    public array $items = [];
    public string $productSearch = '';

    public function mount(?int $id = null): void
    {
        $this->adjustment_date = now()->format('Y-m-d');

        if ($id) {
            $this->adjustmentId = $id;
            $this->editing = true;
            $this->loadAdjustment();
        } else {
            $this->addItem();
        }
    }

    protected function loadAdjustment(): void
    {
        $this->adjustment = InventoryAdjustment::with(['items.product'])->findOrFail($this->adjustmentId);

        $this->warehouse_id = $this->adjustment->warehouse_id;
        $this->adjustment_date = $this->adjustment->adjustment_date->format('Y-m-d');
        $this->adjustment_type = $this->adjustment->adjustment_type;
        $this->status = $this->adjustment->status;
        $this->reason = $this->adjustment->reason ?? '';
        $this->notes = $this->adjustment->notes ?? '';

        $this->items = $this->adjustment->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $item->product->name ?? '',
            'sku' => $item->product->sku ?? '',
            'system_quantity' => $item->system_quantity,
            'counted_quantity' => $item->counted_quantity,
            'difference' => $item->difference,
        ])->toArray();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'product_id' => null,
            'name' => '',
            'sku' => '',
            'system_quantity' => 0,
            'counted_quantity' => 0,
            'difference' => 0,
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
            $this->items[$index]['sku'] = $product->sku;
            $this->items[$index]['system_quantity'] = $product->quantity;
            $this->calculateDifference($index);
        }
    }

    public function calculateDifference(int $index): void
    {
        $item = &$this->items[$index];
        $item['difference'] = $item['counted_quantity'] - $item['system_quantity'];
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'counted_quantity') {
            $index = (int) $parts[0];
            $this->calculateDifference($index);
        }
    }

    public function save(): void
    {
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();

        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product to the adjustment.');
            return;
        }

        $this->items = $validItems;

        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:increase,decrease,count',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.counted_quantity' => 'required|integer|min:0',
        ]);

        $data = [
            'warehouse_id' => $this->warehouse_id,
            'user_id' => Auth::id(),
            'adjustment_date' => $this->adjustment_date,
            'adjustment_type' => $this->adjustment_type,
            'status' => $this->status,
            'reason' => $this->reason ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->adjustmentId) {
            $adjustment = InventoryAdjustment::findOrFail($this->adjustmentId);
            $adjustment->update($data);
            $adjustment->items()->delete();
        } else {
            $data['adjustment_number'] = InventoryAdjustment::generateAdjustmentNumber();
            $adjustment = InventoryAdjustment::create($data);
            $this->adjustmentId = $adjustment->id;
        }

        foreach ($this->items as $item) {
            InventoryAdjustmentItem::create([
                'inventory_adjustment_id' => $adjustment->id,
                'product_id' => $item['product_id'],
                'system_quantity' => $item['system_quantity'],
                'counted_quantity' => $item['counted_quantity'],
                'difference' => $item['counted_quantity'] - $item['system_quantity'],
            ]);
        }

        session()->flash('success', 'Adjustment saved successfully.');
        $this->redirect(route('inventory.adjustments.edit', $adjustment->id), navigate: true);
    }

    public function render()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::query()
            ->when($this->productSearch, fn($q) => $q->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('livewire.inventory.adjustments.form', [
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }
}
