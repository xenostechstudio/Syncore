<?php

namespace App\Livewire\Sales\Invoices;

use App\Enums\SalesOrderState;
use App\Models\Sales\SalesOrder;
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
    ];

    // For compatibility with shared view (not really used for label here)
    public string $mode = 'orders';

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

    public function render()
    {
        $orders = $this->getOrdersQuery()->paginate(12);

        // Reuse the existing Sales Orders index table design
        return view('livewire.sales.orders.index', [
            'orders' => $orders,
        ]);
    }
}
