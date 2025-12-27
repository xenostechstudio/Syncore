<?php

namespace App\Livewire\Inventory\Warehouses;

use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryTransfer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Warehouses')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public int $perPage = 15;

    public string $view = 'grid';

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

    public function delete(int $id): void
    {
        Warehouse::findOrFail($id)->delete();
    }

    public function render()
    {
        $warehouses = Warehouse::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('location', 'like', "%{$this->search}%"))
            ->withCount('products')
            ->withCount(['transfers as transfers_out_count' => function ($query) {
                $query->where('source_warehouse_id', '!=', null);
            }])
            ->latest()
            ->paginate($this->perPage);

        // Get total IN/OUT counts
        $totalTransfersIn = InventoryTransfer::whereNotNull('destination_warehouse_id')->count();
        $totalTransfersOut = InventoryTransfer::whereNotNull('source_warehouse_id')->count();

        return view('livewire.inventory.warehouses.index', [
            'warehouses' => $warehouses,
            'totalTransfersIn' => $totalTransfersIn,
            'totalTransfersOut' => $totalTransfersOut,
        ]);
    }
}
