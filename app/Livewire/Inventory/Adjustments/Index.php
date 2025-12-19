<?php

namespace App\Livewire\Inventory\Adjustments;

use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Stock Adjustment')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $warehouse = '';

    #[Url]
    public string $adjustmentType = '';

    #[Url]
    public string $view = 'list';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $selected = [];
    public bool $selectAll = false;

    public array $visibleColumns = [
        'adjustment' => true,
        'warehouse' => true,
        'type' => true,
        'date' => true,
        'items' => true,
        'status' => true,
    ];

    public function mount(): void
    {
        if (request()->routeIs('inventory.warehouse-in.*')) {
            $this->adjustmentType = 'increase';
        }

        if (request()->routeIs('inventory.warehouse-out.*')) {
            $this->adjustmentType = 'decrease';
        }
    }

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
            $this->selected = InventoryAdjustment::query()
                ->when($this->search, fn($q) => $q->where('adjustment_number', 'like', "%{$this->search}%"))
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
        InventoryAdjustment::findOrFail($id)->delete();
        session()->flash('success', 'Adjustment deleted successfully.');
    }

    public function deleteSelected(): void
    {
        InventoryAdjustment::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected adjustments deleted successfully.');
    }

    public function render()
    {
        $adjustments = InventoryAdjustment::query()
            ->with(['warehouse', 'user', 'items'])
            ->when($this->search, fn($q) => $q->where('adjustment_number', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->when($this->adjustmentType, fn($q) => $q->where('adjustment_type', $this->adjustmentType))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('livewire.inventory.adjustments.index', [
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
        ]);
    }
}
