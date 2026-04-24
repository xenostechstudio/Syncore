<?php

namespace App\Livewire\Purchase\Suppliers;

use App\Exports\SuppliersExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Purchase\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Suppliers')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public int $perPage = 10;

    public string $viewType = 'list';

    public array $visibleColumns = [
        'supplier' => true,
        'contact' => true,
        'email' => true,
        'status' => true,
    ];

    public function mount(): void
    {
        $this->status = 'all';
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = ! $this->visibleColumns[$column];
        }
    }

    public function setView(string $view): void
    {
        $this->viewType = $view;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'sort']);
        $this->status = 'all';
        $this->resetPage();
        $this->clearSelection();
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $suppliers = Supplier::whereIn('id', $this->selected)
            ->withCount(['purchaseOrders as active_po_count' => fn ($q) => $q->whereNotIn('status', ['cancelled', 'received'])])
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($suppliers as $supplier) {
            if ($supplier->active_po_count === 0) {
                $canDelete[] = ['id' => $supplier->id, 'name' => $supplier->name];
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

        $suppliersWithPOs = Supplier::whereIn('id', $this->selected)
            ->whereHas('purchaseOrders', fn ($q) => $q->whereNotIn('status', ['cancelled', 'received']))
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

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Supplier::whereIn('id', $this->selected)->update(['is_active' => true]);
        $this->clearSelection();
        session()->flash('success', "{$count} suppliers activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Supplier::whereIn('id', $this->selected)->update(['is_active' => false]);
        $this->clearSelection();
        session()->flash('success', "{$count} suppliers deactivated.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'suppliers-' . now()->format('Y-m-d') . '.xlsx'
            : 'suppliers-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SuppliersExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return Supplier::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('contact_person', 'like', "%{$this->search}%")))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false));
    }

    protected function getModelClass(): string
    {
        return Supplier::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('created_at', 'asc'),
            'name' => $this->getQuery()->orderBy('name', 'asc'),
            default => $this->getQuery()->orderBy('created_at', 'desc'),
        };

        $total = $query->count();
        $suppliers = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / max(1, $this->perPage));

        return view('livewire.purchase.suppliers.index', [
            'suppliers' => $suppliers,
            'total' => $total,
        ]);
    }
}
