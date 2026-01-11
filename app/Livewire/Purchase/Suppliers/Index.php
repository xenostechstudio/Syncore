<?php

namespace App\Livewire\Purchase\Suppliers;

use App\Exports\SuppliersExport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Purchase\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Suppliers')]
class Index extends Component
{
    use WithManualPagination;

    public string $search = '';
    public string $status = 'all';
    public string $sort = 'latest';
    public string $viewType = 'list';

    #[Url]
    public int $perPage = 10;

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public array $visibleColumns = [
        'supplier' => true,
        'contact' => true,
        'email' => true,
        'status' => true,
    ];

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = !$this->visibleColumns[$column];
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getSuppliersQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->sort = 'latest';
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function setView(string $view): void
    {
        $this->viewType = $view;
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which suppliers can be deleted (no active POs)
        $suppliers = Supplier::whereIn('id', $this->selected)
            ->withCount(['purchaseOrders as active_po_count' => fn($q) => $q->whereNotIn('status', ['cancelled', 'received'])])
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($suppliers as $supplier) {
            if ($supplier->active_po_count === 0) {
                $canDelete[] = [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'reason' => "Has {$supplier->active_po_count} active purchase orders",
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

        // Only delete suppliers without active POs
        $suppliersWithPOs = Supplier::whereIn('id', $this->selected)
            ->whereHas('purchaseOrders', fn($q) => $q->whereNotIn('status', ['cancelled', 'received']))
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $suppliersWithPOs));

        if (empty($deletableIds)) {
            session()->flash('error', 'No suppliers can be deleted. All selected suppliers have active purchase orders.');
            $this->cancelDelete();
            return;
        }

        $count = Supplier::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} suppliers deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Supplier::whereIn('id', $this->selected)
            ->update(['is_active' => true]);

        $this->clearSelection();
        session()->flash('success', "{$count} suppliers activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Supplier::whereIn('id', $this->selected)
            ->update(['is_active' => false]);

        $this->clearSelection();
        session()->flash('success', "{$count} suppliers deactivated.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new SuppliersExport(), 'suppliers-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new SuppliersExport($this->selected), 'suppliers-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function getSuppliersQuery()
    {
        return DB::table('suppliers')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', "%{$this->search}%")
                        ->orWhere('email', 'ilike', "%{$this->search}%")
                        ->orWhere('contact_person', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->sort === 'oldest', fn($q) => $q->orderBy('created_at', 'asc'))
            ->when($this->sort === 'name', fn($q) => $q->orderBy('name', 'asc'))
            ->when($this->sort === 'latest', fn($q) => $q->orderBy('created_at', 'desc'));
    }

    public function render()
    {
        $query = $this->getSuppliersQuery();
        $total = $query->count();
        
        $suppliers = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / $this->perPage);

        return view('livewire.purchase.suppliers.index', [
            'suppliers' => $suppliers,
            'total' => $total,
        ]);
    }
}
