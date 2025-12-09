<?php

namespace App\Livewire\Sales\Orders;

use App\Models\Sales\SalesOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Orders')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public string $sort = 'latest';
    
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Column visibility
    public array $visibleColumns = [
        'order' => true,
        'customer' => true,
        'salesperson' => true,
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

    public function setView(string $view): void
    {
        $this->view = $view;
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
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
    }

    private function getOrdersQuery()
    {
        return SalesOrder::query()
            ->with(['customer', 'user'])
            ->when($this->search, fn($q) => $q->where('order_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'total_high', fn($q) => $q->orderByDesc('total'))
            ->when($this->sort === 'total_low', fn($q) => $q->orderBy('total'));
    }

    public function render()
    {
        $orders = $this->getOrdersQuery()->paginate(12);

        return view('livewire.sales.orders.index', [
            'orders' => $orders,
        ]);
    }
}
