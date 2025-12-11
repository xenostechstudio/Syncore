<?php

namespace App\Livewire\Inventory\Items;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Inventory\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
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
    public int $perPage = 15;
    
    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    public array $visibleColumns = [
        'name' => true,
        'sku' => true,
        'category' => true,
        'stock' => true,
        'price' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Product::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"))
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
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

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
    }

    public function toggleFavorite(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_favorite' => !$product->is_favorite]);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        $this->selected = array_filter($this->selected, fn($s) => $s != $id);
    }

    public function deleteSelected(): void
    {
        Product::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
    }

    public function render()
    {
        $products = Product::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'name', fn($q) => $q->orderBy('name'))
            ->when($this->sort === 'price_high', fn($q) => $q->orderByDesc('selling_price'))
            ->when($this->sort === 'price_low', fn($q) => $q->orderBy('selling_price'))
            ->when($this->sort === 'stock_high', fn($q) => $q->orderByDesc('quantity'))
            ->when($this->sort === 'stock_low', fn($q) => $q->orderBy('quantity'))
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        // Group products by status for kanban view
        $productsByStatus = null;
        if ($this->view === 'kanban') {
            $productsByStatus = Product::query()
                ->with('category')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"))
                ->get()
                ->groupBy('status');
        }

        return view('livewire.inventory.items.index', [
            'items' => $products,
            'itemsByStatus' => $productsByStatus,
        ]);
    }
}
