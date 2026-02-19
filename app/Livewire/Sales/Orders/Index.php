<?php

namespace App\Livewire\Sales\Orders;

use App\Exports\SalesOrdersExport;
use App\Imports\SalesOrdersImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Orders')]
class Index extends Component
{
    use WithManualPagination, WithImport;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $groupBy = '';
    
    #[Url]
    public string $view = 'list';

    #[Url]
    public bool $myQuotations = true;

    #[Url]
    public bool $showStats = false;

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    // Context: 'quotations' (default) or 'orders'
    public string $mode = 'quotations';

    public function mount(): void
    {
        $routeName = request()->route()->getName();
        $this->mode = $routeName === 'sales.orders.all' ? 'orders' : 'quotations';

        $this->myQuotations = $this->mode === 'quotations';
    }

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    private function getStatistics(): array
    {
        $baseQuery = SalesOrder::query()
            ->when(
                $this->mode === 'quotations' && $this->myQuotations,
                fn ($q) => $q->where('user_id', Auth::id())
            );

        // Get counts and totals by status
        $stats = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Calculate totals
        $quotations = ($stats->get('draft')?->count ?? 0) + ($stats->get('confirmed')?->count ?? 0);
        $quotationsTotal = ($stats->get('draft')?->total ?? 0) + ($stats->get('confirmed')?->total ?? 0);
        
        $salesOrders = $stats->get('processing')?->count ?? 0;
        $salesOrdersTotal = $stats->get('processing')?->total ?? 0;

        $toInvoice = (clone $baseQuery)
            ->where('status', 'processing')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_invoiced'))
            ->count();

        $toDeliver = (clone $baseQuery)
            ->where('status', 'processing')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_delivered'))
            ->count();

        return [
            'quotations' => $quotations,
            'quotations_total' => $quotationsTotal,
            'sales_orders' => $salesOrders,
            'sales_orders_total' => $salesOrdersTotal,
            'to_invoice' => $toInvoice,
            'to_deliver' => $toDeliver,
        ];
    }

    // Column visibility
    public array $visibleColumns = [
        'order' => true,
        'customer' => true,
        'salesperson' => true,
        'date' => true,
        'total' => true,
        'status' => true,
        'invoicing' => false,
        'delivery' => false,
    ];

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = !$this->visibleColumns[$column];
        }
    }

    public function setView(string $view): void
    {
        if (! in_array($view, ['list', 'grid', 'kanban'])) {
            return;
        }

        $this->view = $view;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedGroupBy(): void
    {
        $this->resetPage();
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
        $this->reset(['search', 'status', 'sort', 'groupBy']);

        $this->myQuotations = $this->mode === 'quotations';
        $this->resetPage();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which orders can be deleted
        $orders = SalesOrder::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($orders as $order) {
            if (in_array($order->status, ['draft', 'quotation', 'confirmed'])) {
                $canDelete[] = [
                    'id' => $order->id,
                    'name' => $order->order_number,
                    'status' => $order->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $order->id,
                    'name' => $order->order_number,
                    'reason' => "Status is '{$order->status}' - only draft/quotation/confirmed can be deleted",
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

        $count = SalesOrder::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'quotation', 'confirmed'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} orders deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkCancel(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SalesOrder::whereIn('id', $this->selected)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->update(['status' => 'cancelled']);

        $this->clearSelection();
        session()->flash('success', "{$count} orders cancelled successfully.");
    }

    public function bulkConfirm(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SalesOrder::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'quotation'])
            ->update(['status' => 'confirmed']);

        $this->clearSelection();
        session()->flash('success', "{$count} orders confirmed successfully.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new SalesOrdersExport(), 'sales-orders-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new SalesOrdersExport($this->selected), 'sales-orders-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getImportClass(): string
    {
        return SalesOrdersImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['customer', 'order_date', 'expected_delivery_date', 'status', 'payment_terms', 'subtotal', 'tax', 'discount', 'total', 'notes', 'shipping_address'],
            'filename' => 'sales-orders-template.csv',
        ];
    }

    private function getOrdersQuery()
    {
        return SalesOrder::query()
            ->with(['customer', 'user'])
            ->when(
                $this->mode === 'quotations' && $this->myQuotations,
                fn ($q) => $q->where('user_id', Auth::id())
            )
            ->when($this->search, fn($q) => $q->where(fn ($qq) => $qq
                ->where('order_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->mode === 'orders', fn($q) => $q->where('status', 'processing'))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'total_high', fn($q) => $q->orderByDesc('total'))
            ->when($this->sort === 'total_low', fn($q) => $q->orderBy('total'));
    }

    public function render()
    {
        $query = $this->getOrdersQuery();
        
        // Eager load items for kanban view (needed for progress calculations)
        $eagerLoads = ['customer:id,name', 'user:id,name'];
        if ($this->view === 'kanban') {
            $eagerLoads[] = 'items:id,sales_order_id,quantity,quantity_invoiced,quantity_delivered';
        }

        $orders = $query
            ->with($eagerLoads)
            ->withCount(['items', 'invoices', 'deliveryOrders'])
            ->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.sales.orders.index', [
            'orders' => $orders,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
