<?php

namespace App\Livewire\Inventory\Items;

use App\Models\Inventory\InventoryItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Items')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public string $sort = 'latest';
    
    #[Url]
    public int $perPage = 15;
    
    public string $view = 'list';

    public function setView(string $view): void
    {
        $this->view = $view;
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

    public function delete(int $id): void
    {
        InventoryItem::findOrFail($id)->delete();
    }

    public function render()
    {
        $items = InventoryItem::query()
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
            ->paginate($this->perPage);

        return view('livewire.inventory.items.index', [
            'items' => $items,
        ]);
    }
}
