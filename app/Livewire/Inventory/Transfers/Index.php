<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Internal Transfer')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sourceWarehouse = '';

    #[Url]
    public string $destinationWarehouse = '';

    #[Url]
    public string $view = 'list';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $selected = [];
    public bool $selectAll = false;

    public array $visibleColumns = [
        'transfer' => true,
        'source' => true,
        'destination' => true,
        'date' => true,
        'items' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = InventoryTransfer::query()
                ->when($this->search, fn($q) => $q->where('transfer_number', 'like', "%{$this->search}%"))
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        InventoryTransfer::findOrFail($id)->delete();
        session()->flash('success', 'Transfer deleted successfully.');
    }

    public function deleteSelected(): void
    {
        InventoryTransfer::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected transfers deleted successfully.');
    }

    public function render()
    {
        $transfers = InventoryTransfer::query()
            ->with(['sourceWarehouse', 'destinationWarehouse', 'user', 'items'])
            ->when($this->search, fn($q) => $q->where('transfer_number', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sourceWarehouse, fn($q) => $q->where('source_warehouse_id', $this->sourceWarehouse))
            ->when($this->destinationWarehouse, fn($q) => $q->where('destination_warehouse_id', $this->destinationWarehouse))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('livewire.inventory.transfers.index', [
            'transfers' => $transfers,
            'warehouses' => $warehouses,
        ]);
    }
}
