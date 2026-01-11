<?php

namespace App\Livewire\Inventory\Warehouses;

use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;
use App\Livewire\Concerns\WithImport;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryTransfer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Warehouses')]
class Index extends Component
{
    use WithPagination, WithImport;

    #[Url]
    public string $search = '';
    
    #[Url]
    public int $perPage = 15;

    public string $view = 'grid';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Warehouse::query()
                ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
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

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which warehouses can be deleted (no stock)
        $warehouses = Warehouse::whereIn('id', $this->selected)
            ->withCount('products')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($warehouses as $warehouse) {
            if ($warehouse->products_count === 0) {
                $canDelete[] = [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'reason' => "Has {$warehouse->products_count} products in stock",
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

        // Only delete warehouses without stock
        $warehousesWithStock = Warehouse::whereIn('id', $this->selected)
            ->whereHas('products')
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $warehousesWithStock));

        if (empty($deletableIds)) {
            session()->flash('error', 'No warehouses can be deleted. All selected warehouses have products.');
            $this->cancelDelete();
            return;
        }

        $count = Warehouse::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} warehouses deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new WarehousesExport(), 'warehouses-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new WarehousesExport($this->selected), 'warehouses-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getImportClass(): string
    {
        return WarehousesImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'location', 'contact_info'],
            'filename' => 'warehouses-template.csv',
        ];
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
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('location', 'ilike', "%{$this->search}%"))
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
