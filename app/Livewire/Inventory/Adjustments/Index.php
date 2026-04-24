<?php

namespace App\Livewire\Inventory\Adjustments;

use App\Exports\AdjustmentsExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Stock Adjustment')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public string $warehouse = '';

    #[Url]
    public string $adjustmentType = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

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

    public function updatedWarehouse(): void
    {
        $this->resetPage();
    }

    public function updatedAdjustmentType(): void
    {
        $this->resetPage();
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

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $adjustments = InventoryAdjustment::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($adjustments as $adjustment) {
            $statusValue = $adjustment->status?->value ?? $adjustment->status;
            if (in_array($statusValue, ['draft', 'pending'], true)) {
                $canDelete[] = [
                    'id' => $adjustment->id,
                    'name' => $adjustment->adjustment_number,
                    'status' => $statusValue,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $adjustment->id,
                    'name' => $adjustment->adjustment_number,
                    'reason' => "Status is '{$statusValue}' - only draft/pending can be deleted",
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
        $filename = empty($this->selected)
            ? 'adjustments-' . now()->format('Y-m-d') . '.xlsx'
            : 'adjustments-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AdjustmentsExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return InventoryAdjustment::query()
            ->when($this->search, fn ($q) => $q->where('adjustment_number', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->warehouse, fn ($q) => $q->where('warehouse_id', $this->warehouse))
            ->when($this->adjustmentType, fn ($q) => $q->where('adjustment_type', $this->adjustmentType));
    }

    protected function getModelClass(): string
    {
        return InventoryAdjustment::class;
    }

    public function render()
    {
        $adjustments = $this->getQuery()
            ->with(['warehouse', 'user', 'items'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.inventory.adjustments.index', [
            'adjustments' => $adjustments,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }
}
