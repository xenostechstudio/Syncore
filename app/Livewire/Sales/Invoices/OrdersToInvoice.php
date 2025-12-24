<?php

namespace App\Livewire\Sales\Invoices;

use App\Enums\SalesOrderState;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Orders to Invoice')]
class OrdersToInvoice extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $groupBy = '';

    public string $view = 'list';

    #[Url]
    public bool $showStats = false;

    public array $selected = [];
    public bool $selectAll = false;

    // Match Sales Orders index visible columns
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

    // For compatibility with shared view (not really used for label here)
    public string $mode = 'orders';

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = ! $this->visibleColumns[$column];
        }
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
            $this->selected = $this->getOrdersQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
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

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->resetPage();
    }

    private function getOrdersQuery()
    {
        return SalesOrder::query()
            ->with(['customer', 'user'])
            ->where('status', SalesOrderState::SALES_ORDER->value)
            ->when($this->search, fn ($q) => $q->where(fn ($qq) => $qq
                ->where('order_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn ($q) => $q->latest())
            ->when($this->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($this->sort === 'total_high', fn ($q) => $q->orderByDesc('total'))
            ->when($this->sort === 'total_low', fn ($q) => $q->orderBy('total'));
    }

    private function getStatistics(): array
    {
        $baseQuery = SalesOrder::query()
            ->where('status', SalesOrderState::SALES_ORDER->value);

        // Get counts and totals
        $stats = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $salesOrders = $stats->get(SalesOrderState::SALES_ORDER->value)?->count ?? 0;
        $salesOrdersTotal = $stats->get(SalesOrderState::SALES_ORDER->value)?->total ?? 0;

        $toInvoice = (clone $baseQuery)
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_invoiced'))
            ->count();

        $toDeliver = (clone $baseQuery)
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_delivered'))
            ->count();

        return [
            'quotations' => 0,
            'quotations_total' => 0,
            'sales_orders' => $salesOrders,
            'sales_orders_total' => $salesOrdersTotal,
            'to_invoice' => $toInvoice,
            'to_deliver' => $toDeliver,
        ];
    }

    public function render()
    {
        $orders = $this->getOrdersQuery()
            ->with(['items', 'invoices', 'deliveryOrders'])
            ->paginate(12);

        // Reuse the existing Sales Orders index table design
        return view('livewire.sales.orders.index', [
            'orders' => $orders,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
