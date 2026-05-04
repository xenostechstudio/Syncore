<?php

namespace App\Livewire\Sales\Products;

use App\Exports\ProductsExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Livewire\Concerns\WithPermissions;
use App\Models\Inventory\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Products')]
class Index extends Component
{
    use WithIndexComponent, WithPermissions;

    public array $openGroups = [];

    public array $visibleColumns = [
        'name' => true,
        'sku' => true,
        'price' => true,
        'stock' => true,
        'status' => true,
    ];

    public function toggleGroup(string $groupId): void
    {
        if (in_array($groupId, $this->openGroups, true)) {
            $this->openGroups = array_values(array_filter($this->openGroups, fn ($id) => $id !== $groupId));
            return;
        }

        $this->openGroups[] = $groupId;
    }

    public function updatedGroupBy(): void
    {
        $this->resetPage();
        $this->openGroups = [];
    }

    public function bulkActivate(): void
    {
        $this->authorizePermission('sales.edit');

        if (empty($this->selected)) {
            return;
        }

        $count = Product::whereIn('id', $this->selected)->update(['status' => 'in_stock']);
        $this->clearSelection();
        session()->flash('success', "{$count} products activated.");
    }

    public function bulkDeactivate(): void
    {
        $this->authorizePermission('sales.edit');

        if (empty($this->selected)) {
            return;
        }

        $count = Product::whereIn('id', $this->selected)->update(['status' => 'out_of_stock']);
        $this->clearSelection();
        session()->flash('success', "{$count} products deactivated.");
    }

    public function bulkDelete(): void
    {
        $this->authorizePermission('sales.delete');

        if (empty($this->selected)) {
            return;
        }

        $count = Product::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
        session()->flash('success', "{$count} products deleted.");
    }

    public function toggleFavorite(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->is_favorite = ! $product->is_favorite;
        $product->save();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            session()->flash('error', 'Please select at least one product to export.');
            return;
        }

        $ids = array_map('intval', $this->selected);
        $this->clearSelection();

        return Excel::download(new ProductsExport($ids), 'products-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getQuery()
    {
        return Product::query()
            ->when($this->groupBy === 'category', fn ($q) => $q->with('category'))
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    private function groupLabel(string $groupKey): string
    {
        if ($this->groupBy === 'status') {
            return ucfirst(str_replace('_', ' ', $groupKey));
        }

        return $groupKey;
    }

    private function groupProducts($products): array
    {
        if (empty($this->groupBy)) {
            return [];
        }

        if ($this->groupBy === 'status') {
            $grouped = $products->groupBy(fn ($p) => $p->status ?: 'unknown');
            $orderedKeys = ['in_stock', 'low_stock', 'out_of_stock', 'unknown'];
            $result = [];

            foreach ($orderedKeys as $key) {
                if (! $grouped->has($key)) {
                    continue;
                }
                $result[] = [
                    'id' => md5("{$this->groupBy}:{$key}"),
                    'label' => $this->groupLabel($key),
                    'items' => $grouped->get($key)->values(),
                ];
            }

            foreach ($grouped as $key => $items) {
                if (in_array($key, $orderedKeys, true)) {
                    continue;
                }
                $result[] = [
                    'id' => md5("{$this->groupBy}:{$key}"),
                    'label' => $this->groupLabel((string) $key),
                    'items' => $items->values(),
                ];
            }

            return $result;
        }

        if ($this->groupBy === 'category') {
            return $products->groupBy(fn ($p) => $p->category?->name ?: 'Uncategorized')
                ->sortKeys()
                ->map(fn ($items, $key) => [
                    'id' => md5("{$this->groupBy}:{$key}"),
                    'label' => (string) $key,
                    'items' => $items->values(),
                ])
                ->values()
                ->all();
        }

        return [];
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->oldest(),
            'name' => $this->getQuery()->orderBy('name'),
            'price_high' => $this->getQuery()->orderByDesc('selling_price'),
            'price_low' => $this->getQuery()->orderBy('selling_price'),
            'stock_high' => $this->getQuery()->orderByDesc('quantity'),
            'stock_low' => $this->getQuery()->orderBy('quantity'),
            default => $this->getQuery()->latest(),
        };

        if ($this->view === 'kanban') {
            $allProducts = $query->get();
            $groupedProducts = [
                'in_stock' => $allProducts->where('status', 'in_stock')->values(),
                'low_stock' => $allProducts->where('status', 'low_stock')->values(),
                'out_of_stock' => $allProducts->where('status', 'out_of_stock')->values(),
            ];

            $products = $this->getQuery()->paginate(15, ['*'], 'page', $this->page);

            return view('livewire.sales.products.index', [
                'products' => $products,
                'groupedProducts' => $groupedProducts,
                'groupedListProducts' => [],
            ]);
        }

        $products = $query->paginate(15, ['*'], 'page', $this->page);

        $groupedListProducts = [];
        if ($this->view === 'list' && ! empty($this->groupBy)) {
            $groupedListProducts = $this->groupProducts($products->getCollection());

            if (empty($this->openGroups) && ! empty($groupedListProducts)) {
                $this->openGroups = [$groupedListProducts[0]['id']];
            }
        }

        return view('livewire.sales.products.index', [
            'products' => $products,
            'groupedProducts' => [],
            'groupedListProducts' => $groupedListProducts,
        ]);
    }
}
