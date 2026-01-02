<?php

namespace App\Livewire\Sales\Orders;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Orders')]
class Index extends Component
{
    use WithManualPagination;

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
        $orders = $this->getOrdersQuery()
            ->with(['items', 'invoices', 'deliveryOrders'])
            ->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.sales.orders.index', [
            'orders' => $orders,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
