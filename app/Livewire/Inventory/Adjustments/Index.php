<?php

namespace App\Livewire\Inventory\Adjustments;

use App\Exports\AdjustmentsExport;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

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

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

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
        $this->clearSelection();
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getAdjustmentsQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'warehouse']);
        $this->resetPage();
        $this->clearSelection();
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

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $adjustments = InventoryAdjustment::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($adjustments as $adjustment) {
            if (in_array($adjustment->status, ['draft', 'pending'])) {
                $canDelete[] = [
                    'id' => $adjustment->id,
                    'name' => $adjustment->adjustment_number,
                    'status' => $adjustment->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $adjustment->id,
                    'name' => $adjustment->adjustment_number,
                    'reason' => "Status is '{$adjustment->status}' - only draft/pending can be deleted",
                ];
            }
        }

        $this->deleteValidation = [
            'canDelete' => $canDelete,
            'cannotDelete' => $cannotDelete,
            'totalSelected' => count($this->selected),
        ];

        $this->showDeleteConfirm = true;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = InventoryAdjustment::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'pending'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} adjustments deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = InventoryAdjustment::whereIn('id', $this->selected)->update(['status' => $status]);

        $this->clearSelection();
        session()->flash('success', "{$count} adjustments updated to {$status}.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new AdjustmentsExport(), 'adjustments-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new AdjustmentsExport($this->selected), 'adjustments-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getAdjustmentsQuery()
    {
        return InventoryAdjustment::query()
            ->when($this->search, fn($q) => $q->where('adjustment_number', 'ilike', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->when($this->adjustmentType, fn($q) => $q->where('adjustment_type', $this->adjustmentType));
    }

    public function render()
    {
        $adjustments = $this->getAdjustmentsQuery()
            ->with(['warehouse', 'user', 'items'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('livewire.inventory.adjustments.index', [
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
        ]);
    }
}
