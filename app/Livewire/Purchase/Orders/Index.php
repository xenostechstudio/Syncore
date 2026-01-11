<?php

namespace App\Livewire\Purchase\Orders;

use App\Exports\PurchaseOrdersExport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Purchase\PurchaseOrder;
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
        'order' => true,
        'supplier' => true,
        'date' => true,
        'total' => true,
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
            $this->selected = $this->getOrdersQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
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

        // Validate which POs can be deleted
        $orders = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($orders as $order) {
            if (in_array($order->status, ['draft', 'rfq'])) {
                $canDelete[] = [
                    'id' => $order->id,
                    'name' => $order->reference,
                    'status' => $order->status,
                ];
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
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
        if (empty($this->selected)) {
            return Excel::download(new PurchaseOrdersExport(), 'purchase-orders-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new PurchaseOrdersExport($this->selected), 'purchase-orders-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function getOrdersQuery()
    {
        return DB::table('purchase_rfqs')
            ->where('status', 'purchase_order')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference', 'ilike', "%{$this->search}%")
                        ->orWhere('supplier_name', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->sort === 'oldest', fn($q) => $q->orderBy('created_at', 'asc'))
            ->when($this->sort === 'reference', fn($q) => $q->orderBy('reference', 'asc'))
            ->when($this->sort === 'latest', fn($q) => $q->orderBy('created_at', 'desc'));
    }

    public function render()
    {
        $query = $this->getOrdersQuery();
        $total = $query->count();
        
        $orders = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / $this->perPage);

        return view('livewire.purchase.orders.index', [
            'orders' => $orders,
            'total' => $total,
        ]);
    }
}
