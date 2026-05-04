<?php

namespace App\Livewire\Sales\Orders;

use App\Exports\SalesOrdersExport;
use App\Imports\SalesOrdersImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithPermissions;
use App\Livewire\Concerns\WithIndexComponent;
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
    use WithIndexComponent, WithImport, WithPermissions;

    #[Url]
    public bool $myQuotations = true;

    public string $mode = 'quotations';

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

    public function mount(): void
    {
        $routeName = request()->route()?->getName();
        $this->mode = $routeName === 'sales.orders.all' ? 'orders' : 'quotations';
        $this->myQuotations = $this->mode === 'quotations';
    }

    public function updatedMyQuotations(): void
    {
        $this->resetPage();
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = ! $this->visibleColumns[$column];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->myQuotations = $this->mode === 'quotations';
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        $count = 0;
        // Counted as "active" when the user has departed from the page's
        // default for the My/All toggle.
        if ($this->mode === 'quotations' && ! $this->myQuotations) {
            $count++;
        }
        if ($this->mode !== 'quotations' && $this->myQuotations) {
            $count++;
        }
        return $count;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $orders = SalesOrder::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($orders as $order) {
            $statusValue = $order->status?->value ?? $order->status;
            if (in_array($statusValue, ['draft', 'quotation', 'confirmed'], true)) {
                $canDelete[] = ['id' => $order->id, 'name' => $order->order_number, 'status' => $statusValue];
            } else {
                $cannotDelete[] = [
                    'id' => $order->id,
                    'name' => $order->order_number,
                    'reason' => "Status is '{$statusValue}' - only draft/quotation/confirmed can be deleted",
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
        $this->authorizePermission('sales.delete');

        if (empty($this->selected)) {
            return;
        }

        $count = SalesOrder::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'quotation', 'confirmed'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} orders deleted successfully.");
    }

    public function bulkCancel(): void
    {
        $this->authorizePermission('sales.cancel');

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
        $this->authorizePermission('sales.confirm');

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
        $filename = empty($this->selected)
            ? 'sales-orders-' . now()->format('Y-m-d') . '.xlsx'
            : 'sales-orders-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SalesOrdersExport($this->selected ?: null), $filename);
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

    protected function getStatistics(): array
    {
        $baseQuery = SalesOrder::query()
            ->when(
                $this->mode === 'quotations' && $this->myQuotations,
                fn ($q) => $q->where('user_id', Auth::id())
            );

        $stats = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $quotations = ($stats->get('draft')?->count ?? 0) + ($stats->get('confirmed')?->count ?? 0);
        $quotationsTotal = ($stats->get('draft')?->total ?? 0) + ($stats->get('confirmed')?->total ?? 0);

        $salesOrders = $stats->get('processing')?->count ?? 0;
        $salesOrdersTotal = $stats->get('processing')?->total ?? 0;

        $toInvoice = (clone $baseQuery)
            ->where('status', 'processing')
            ->whereHas('items', fn ($q) => $q->whereRaw('quantity > quantity_invoiced'))
            ->count();

        $toDeliver = (clone $baseQuery)
            ->where('status', 'processing')
            ->whereHas('items', fn ($q) => $q->whereRaw('quantity > quantity_delivered'))
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

    protected function getQuery()
    {
        return SalesOrder::query()
            ->with(['customer', 'user'])
            ->when(
                $this->mode === 'quotations' && $this->myQuotations,
                fn ($q) => $q->where('user_id', Auth::id())
            )
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('order_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$this->search}%"))))
            ->when($this->mode === 'orders', fn ($q) => $q->where('status', 'processing'))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return SalesOrder::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->oldest(),
            'total_high' => $this->getQuery()->orderByDesc('total'),
            'total_low' => $this->getQuery()->orderBy('total'),
            default => $this->getQuery()->latest(),
        };

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
