<?php

namespace App\Livewire\Inventory\Adjustments;

use App\Livewire\Concerns\WithNotes;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryAdjustmentItem;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Stock Adjustment')]
class Form extends Component
{
    use WithNotes;
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

    protected function getNotableModel()
    {
        return $this->adjustmentId ? InventoryAdjustment::find($this->adjustmentId) : null;
    }

    public function mount(?int $id = null): void
    {
        $this->adjustment_date = now()->format('Y-m-d');

        if ($id) {
            $this->adjustmentId = $id;
            $this->editing = true;
            $this->loadAdjustment();
        } else {
            if (request()->routeIs('inventory.warehouse-in.*')) {
                $this->adjustment_type = 'increase';
            }

            if (request()->routeIs('inventory.warehouse-out.*')) {
                $this->adjustment_type = 'decrease';
            }
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
            'counted_quantity' => in_array($this->adjustment_type, ['increase', 'decrease'], true) ? 1 : 0,
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
            $this->items[$index]['system_quantity'] = $this->warehouse_id
                ? (int) (InventoryStock::query()
                    ->where('warehouse_id', $this->warehouse_id)
                    ->where('product_id', $product->id)
                    ->value('quantity') ?? 0)
                : 0;
            $this->calculateDifference($index);
        }
    }

    public function updatedWarehouseId(): void
    {
        if (! $this->warehouse_id) {
            return;
        }

        $productIds = collect($this->items)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($productIds)) {
            return;
        }

        $stocks = InventoryStock::query()
            ->where('warehouse_id', $this->warehouse_id)
            ->whereIn('product_id', $productIds)
            ->pluck('quantity', 'product_id');

        foreach ($this->items as $index => $item) {
            $pid = $item['product_id'] ?? null;
            if (! $pid) {
                continue;
            }

            $this->items[$index]['system_quantity'] = (int) ($stocks[$pid] ?? 0);
            $this->calculateDifference((int) $index);
        }
    }

    public function calculateDifference(int $index): void
    {
        $item = &$this->items[$index];

        if ($this->adjustment_type === 'increase') {
            $item['difference'] = (int) ($item['counted_quantity'] ?? 0);
            return;
        }

        if ($this->adjustment_type === 'decrease') {
            $item['difference'] = -1 * (int) ($item['counted_quantity'] ?? 0);
            return;
        }

        $item['difference'] = (int) ($item['counted_quantity'] ?? 0) - (int) ($item['system_quantity'] ?? 0);
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'counted_quantity') {
            $index = (int) $parts[0];
            $this->calculateDifference($index);
        }
    }

    public function updatedAdjustmentType(): void
    {
        foreach (array_keys($this->items) as $index) {
            $this->calculateDifference((int) $index);
        }
    }

    public function save(): void
    {
        $adjustment = $this->persist();

        $editRoute = request()->routeIs('inventory.warehouse-in.*')
            ? 'inventory.warehouse-in.edit'
            : (request()->routeIs('inventory.warehouse-out.*') ? 'inventory.warehouse-out.edit' : 'inventory.adjustments.edit');

        session()->flash('success', 'Adjustment saved successfully.');
        $this->redirect(route($editRoute, $adjustment->id), navigate: true);
    }

    public function validateAndPost(): void
    {
        try {
            $adjustment = $this->persist();
            $adjustment->post();

            $editRoute = request()->routeIs('inventory.warehouse-in.*')
                ? 'inventory.warehouse-in.edit'
                : (request()->routeIs('inventory.warehouse-out.*') ? 'inventory.warehouse-out.edit' : 'inventory.adjustments.edit');

            $this->status = $adjustment->status;
            session()->flash('success', 'Stock updated successfully.');
            $this->redirect(route($editRoute, $adjustment->id), navigate: true);
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to validate: ' . $e->getMessage());
        }
    }

    private function persist(): InventoryAdjustment
    {
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();

        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product to the adjustment.');
            throw new \RuntimeException('No products selected.');
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

        return DB::transaction(function () {
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
                $adjustment = InventoryAdjustment::create($data);
                $this->adjustmentId = $adjustment->id;
            }

            foreach ($this->items as $item) {
                $counted = (int) ($item['counted_quantity'] ?? 0);
                $system = (int) ($item['system_quantity'] ?? 0);

                $difference = match ($this->adjustment_type) {
                    'increase' => $counted,
                    'decrease' => -1 * $counted,
                    default => $counted - $system,
                };

                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'product_id' => $item['product_id'],
                    'system_quantity' => $system,
                    'counted_quantity' => $counted,
                    'difference' => $difference,
                ]);
            }

            return $adjustment;
        });
    }

    public function duplicate(): void
    {
        if (!$this->adjustmentId) {
            session()->flash('error', 'Please save the adjustment first.');
            return;
        }

        $adjustment = InventoryAdjustment::with('items')->findOrFail($this->adjustmentId);

        DB::transaction(function () use ($adjustment) {
            $newAdjustment = InventoryAdjustment::create([
                'warehouse_id' => $adjustment->warehouse_id,
                'user_id' => Auth::id(),
                'adjustment_date' => now()->format('Y-m-d'),
                'adjustment_type' => $adjustment->adjustment_type,
                'status' => 'draft',
                'reason' => $adjustment->reason,
                'notes' => $adjustment->notes,
            ]);

            foreach ($adjustment->items as $item) {
                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $newAdjustment->id,
                    'product_id' => $item->product_id,
                    'system_quantity' => $item->system_quantity,
                    'counted_quantity' => $item->counted_quantity,
                    'difference' => $item->difference,
                ]);
            }

            session()->flash('success', 'Adjustment duplicated successfully.');
            $this->redirect(route('inventory.adjustments.edit', $newAdjustment->id), navigate: true);
        });
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
