<?php

namespace App\Livewire\Sales\Customers;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Customers')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';
    
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Filters
    public bool $filterActive = false;
    public bool $filterInactive = false;
    public bool $filterWithOrders = false;

    // Group By
    public string $groupBy = '';

    // Column visibility
    public array $visibleColumns = [
        'customer' => true,
        'contact' => true,
        'location' => true,
        'orders' => true,
        'total' => true,
        'status' => true,
    ];

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
            $this->selected = $this->getCustomersQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
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
        $this->reset(['search', 'status', 'sort', 'filterActive', 'filterInactive', 'filterWithOrders', 'groupBy']);
        $this->resetPage();
    }

    public function deleteSelected(): void
    {
        Customer::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected customers deleted successfully.');
    }

    private function getCustomersQuery()
    {
        return Customer::query()
            ->withCount('salesOrders as orders_count')
            ->withSum(['salesOrders' => fn($q) => $q->where('status', 'delivered')], 'total')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->filterActive, fn($q) => $q->where('status', 'active'))
            ->when($this->filterInactive, fn($q) => $q->where('status', 'inactive'))
            ->when($this->filterWithOrders, fn($q) => $q->has('salesOrders'))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name'))
            ->when($this->sort === 'name_desc', fn($q) => $q->orderByDesc('name'));
    }

    public function render()
    {
        $customers = $this->getCustomersQuery()->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.sales.customers.index', [
            'customers' => $customers,
        ]);
    }
}
