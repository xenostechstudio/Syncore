<?php

namespace App\Livewire\Sales\Products;

use App\Models\Inventory\InventoryItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Products')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    public string $view = 'list';

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
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
        $this->clearSelection();
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
        $item = InventoryItem::findOrFail($id);
        $item->is_favorite = ! $item->is_favorite;
        $item->save();
    }

    private function getProductsQuery()
    {
        return InventoryItem::query()
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
        $products = $this->getProductsQuery()->paginate(15);

        return view('livewire.sales.products.index', [
            'products' => $products,
        ]);
    }
}
