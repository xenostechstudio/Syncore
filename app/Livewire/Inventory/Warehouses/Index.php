<?php

namespace App\Livewire\Inventory\Warehouses;

use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Warehouses')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    #[Url]
    public int $perPage = 15;

    public function mount(): void
    {
        $this->view = 'grid';
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Warehouse::findOrFail($id)->delete();
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $warehouses = Warehouse::whereIn('id', $this->selected)
            ->withCount('products')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($warehouses as $warehouse) {
            if ($warehouse->products_count === 0) {
                $canDelete[] = ['id' => $warehouse->id, 'name' => $warehouse->name];
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

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'warehouses-' . now()->format('Y-m-d') . '.xlsx'
            : 'warehouses-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new WarehousesExport($this->selected ?: null), $filename);
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

    protected function getQuery()
    {
        return Warehouse::query()
            ->withCount('products')
            ->withCount(['transfers as transfers_out_count' => function ($query) {
                $query->where('source_warehouse_id', '!=', null);
            }])
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('location', 'like', "%{$this->search}%")));
    }

    protected function getModelClass(): string
    {
        return Warehouse::class;
    }

    public function render()
    {
        $warehouses = $this->getQuery()
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.inventory.warehouses.index', [
            'warehouses' => $warehouses,
            'totalTransfersIn' => InventoryTransfer::whereNotNull('destination_warehouse_id')->count(),
            'totalTransfersOut' => InventoryTransfer::whereNotNull('source_warehouse_id')->count(),
        ]);
    }
}
