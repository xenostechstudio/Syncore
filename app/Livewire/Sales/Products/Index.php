<?php

namespace App\Livewire\Sales\Products;

use App\Exports\ProductsExport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Inventory\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Products')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $groupBy = '';

    public string $view = 'list'; // list, grid, kanban

    public array $openGroups = [];

    public array $selected = [];
    public bool $selectAll = false;

    public array $visibleColumns = [
        'name' => true,
        'sku' => true,
        'price' => true,
        'stock' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedGroupBy(): void
    {
        $this->resetPage();
        $this->openGroups = [];
    }

    public function toggleGroup(string $groupId): void
    {
        if (in_array($groupId, $this->openGroups, true)) {
            $this->openGroups = array_values(array_filter(
                $this->openGroups,
                fn ($id) => $id !== $groupId
            ));

            return;
        }

        $this->openGroups[] = $groupId;
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getProductsQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
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

    private function getProductsQuery()
    {
        return Product::query()
            ->when($this->groupBy === 'category', fn ($q) => $q->with('category'))
            ->when($this->search, fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('sku', 'ilike', "%{$this->search}%")
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn ($q) => $q->latest())
            ->when($this->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($this->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($this->sort === 'price_high', fn ($q) => $q->orderByDesc('selling_price'))
            ->when($this->sort === 'price_low', fn ($q) => $q->orderBy('selling_price'))
            ->when($this->sort === 'stock_high', fn ($q) => $q->orderByDesc('quantity'))
            ->when($this->sort === 'stock_low', fn ($q) => $q->orderBy('quantity'));
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

                $label = $this->groupLabel($key);
                $result[] = [
                    'id' => md5("{$this->groupBy}:{$key}"),
                    'label' => $label,
                    'items' => $grouped->get($key)->values(),
                ];
            }

            foreach ($grouped as $key => $items) {
                if (in_array($key, $orderedKeys, true)) {
                    continue;
                }

                $label = $this->groupLabel((string) $key);
                $result[] = [
                    'id' => md5("{$this->groupBy}:{$key}"),
                    'label' => $label,
                    'items' => $items->values(),
                ];
            }

            return $result;
        }

        if ($this->groupBy === 'category') {
            $grouped = $products->groupBy(fn ($p) => $p->category?->name ?: 'Uncategorized');

            return $grouped
                ->sortKeys()
                ->map(function ($items, $key) {
                    return [
                        'id' => md5("{$this->groupBy}:{$key}"),
                        'label' => (string) $key,
                        'items' => $items->values(),
                    ];
                })
                ->values()
                ->all();
        }

        return [];
    }

    public function render()
    {
        // For kanban view, get all products (no pagination) grouped by status
        if ($this->view === 'kanban') {
            $allProducts = $this->getProductsQuery()->get();
            $groupedProducts = [
                'in_stock' => $allProducts->where('status', 'in_stock')->values(),
                'low_stock' => $allProducts->where('status', 'low_stock')->values(),
                'out_of_stock' => $allProducts->where('status', 'out_of_stock')->values(),
            ];
            
            // Paginate current kanban view page for header info
            $products = $this->getProductsQuery()->paginate(15, ['*'], 'page', $this->page);
            
            return view('livewire.sales.products.index', [
                'products' => $products,
                'groupedProducts' => $groupedProducts,
                'groupedListProducts' => [],
            ]);
        }

        $products = $this->getProductsQuery()->paginate(15, ['*'], 'page', $this->page);

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
