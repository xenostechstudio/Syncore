<?php

namespace App\Livewire\Purchase\Orders;

use App\Exports\PurchaseOrdersExport;
use App\Livewire\Concerns\WithIndexComponent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Purchase Orders')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public int $perPage = 10;

    public string $viewType = 'list';

    public array $visibleColumns = [
        'order' => true,
        'supplier' => true,
        'date' => true,
        'total' => true,
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

        $orders = DB::table('purchase_rfqs')->whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($orders as $order) {
            if (in_array($order->status, ['draft', 'rfq'], true)) {
                $canDelete[] = ['id' => $order->id, 'name' => $order->reference, 'status' => $order->status];
            } else {
                $cannotDelete[] = [
                    'id' => $order->id,
                    'name' => $order->reference,
                    'reason' => "Status is '{$order->status}' - only draft/rfq can be deleted",
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

        $count = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'rfq'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} purchase orders deleted successfully.");
    }

    public function bulkConfirm(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->where('status', 'rfq')
            ->update(['status' => 'purchase_order']);

        $this->clearSelection();
        session()->flash('success', "{$count} RFQs confirmed as purchase orders.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'purchase-orders-' . now()->format('Y-m-d') . '.xlsx'
            : 'purchase-orders-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PurchaseOrdersExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return DB::table('purchase_rfqs')
            ->where('status', 'purchase_order')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhere('supplier_name', 'like', "%{$this->search}%")));
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('created_at', 'asc'),
            'reference' => $this->getQuery()->orderBy('reference', 'asc'),
            default => $this->getQuery()->orderBy('created_at', 'desc'),
        };

        $total = $query->count();
        $orders = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / max(1, $this->perPage));

        return view('livewire.purchase.orders.index', [
            'orders' => $orders,
            'total' => $total,
        ]);
    }
}
