<?php

namespace App\Livewire\Inventory\Transfers;

use App\Exports\TransfersExport;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

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

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

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
        $this->clearSelection();
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getTransfersQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sourceWarehouse', 'destinationWarehouse']);
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

        $transfers = InventoryTransfer::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($transfers as $transfer) {
            if (in_array($transfer->status, ['draft', 'pending'])) {
                $canDelete[] = [
                    'id' => $transfer->id,
                    'name' => $transfer->transfer_number,
                    'status' => $transfer->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $transfer->id,
                    'name' => $transfer->transfer_number,
                    'reason' => "Status is '{$transfer->status}' - only draft/pending can be deleted",
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

        $count = InventoryTransfer::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'pending'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} transfers deleted successfully.");
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

        $count = InventoryTransfer::whereIn('id', $this->selected)->update(['status' => $status]);

        $this->clearSelection();
        session()->flash('success', "{$count} transfers updated to {$status}.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new TransfersExport(), 'transfers-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new TransfersExport($this->selected), 'transfers-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getTransfersQuery()
    {
        return InventoryTransfer::query()
            ->when($this->search, fn($q) => $q->where('transfer_number', 'ilike', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sourceWarehouse, fn($q) => $q->where('source_warehouse_id', $this->sourceWarehouse))
            ->when($this->destinationWarehouse, fn($q) => $q->where('destination_warehouse_id', $this->destinationWarehouse));
    }

    public function render()
    {
        $transfers = $this->getTransfersQuery()
            ->with(['sourceWarehouse', 'destinationWarehouse', 'user', 'items'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('livewire.inventory.transfers.index', [
            'transfers' => $transfers,
            'warehouses' => $warehouses,
        ]);
    }
}
