<?php

namespace App\Livewire\Inventory\Transfers;

use App\Exports\TransfersExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Livewire\Concerns\WithPermissions;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Internal Transfer')]
class Index extends Component
{
    use WithIndexComponent, WithPermissions;

    #[Url]
    public string $sourceWarehouse = '';

    #[Url]
    public string $destinationWarehouse = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public array $visibleColumns = [
        'transfer' => true,
        'source' => true,
        'destination' => true,
        'date' => true,
        'items' => true,
        'status' => true,
    ];

    public function updatedSourceWarehouse(): void
    {
        $this->resetPage();
    }

    public function updatedDestinationWarehouse(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sourceWarehouse', 'destinationWarehouse']);
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        $count = 0;
        if ($this->sourceWarehouse !== '') {
            $count++;
        }
        if ($this->destinationWarehouse !== '') {
            $count++;
        }

        return $count;
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

        $transfers = InventoryTransfer::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($transfers as $transfer) {
            $statusValue = $transfer->status?->value ?? $transfer->status;
            if (in_array($statusValue, ['draft', 'pending'], true)) {
                $canDelete[] = [
                    'id' => $transfer->id,
                    'name' => $transfer->transfer_number,
                    'status' => $statusValue,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $transfer->id,
                    'name' => $transfer->transfer_number,
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
        $this->authorizePermission('inventory.delete');

        if (empty($this->selected)) {
            return;
        }

        $count = InventoryTransfer::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'pending'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} transfers deleted successfully.");
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = InventoryTransfer::whereIn('id', $this->selected)->update(['status' => $status]);

        $this->clearSelection();
        session()->flash('success', "{$count} transfers updated to {$status}.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'transfers-' . now()->format('Y-m-d') . '.xlsx'
            : 'transfers-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new TransfersExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return InventoryTransfer::query()
            ->when($this->search, fn ($q) => $q->where('transfer_number', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->sourceWarehouse, fn ($q) => $q->where('source_warehouse_id', $this->sourceWarehouse))
            ->when($this->destinationWarehouse, fn ($q) => $q->where('destination_warehouse_id', $this->destinationWarehouse));
    }

    protected function getModelClass(): string
    {
        return InventoryTransfer::class;
    }

    public function render()
    {
        $transfers = $this->getQuery()
            ->with(['sourceWarehouse', 'destinationWarehouse', 'user', 'items'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.inventory.transfers.index', [
            'transfers' => $transfers,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }
}
