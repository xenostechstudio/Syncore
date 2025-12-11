<?php

namespace App\Livewire\Sales\Products;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Inventory\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

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

    private function getProductsQuery()
    {
        return Product::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn ($q) => $q->latest())
            ->when($this->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($this->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($this->sort === 'price_high', fn ($q) => $q->orderByDesc('selling_price'))
            ->when($this->sort === 'price_low', fn ($q) => $q->orderBy('selling_price'))
            ->when($this->sort === 'stock_high', fn ($q) => $q->orderByDesc('quantity'))
            ->when($this->sort === 'stock_low', fn ($q) => $q->orderBy('quantity'));
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
            ]);
        }

        $products = $this->getProductsQuery()->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.products.index', [
            'products' => $products,
            'groupedProducts' => [],
        ]);
    }
}
