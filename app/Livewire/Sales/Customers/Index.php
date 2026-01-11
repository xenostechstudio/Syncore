<?php

namespace App\Livewire\Sales\Customers;

use App\Exports\CustomersExport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

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

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

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
        $this->confirmBulkDelete();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which customers can be deleted (no active orders)
        $customers = Customer::whereIn('id', $this->selected)
            ->withCount(['salesOrders as active_orders_count' => fn($q) => $q->whereNotIn('status', ['cancelled', 'delivered'])])
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($customers as $customer) {
            if ($customer->active_orders_count === 0) {
                $canDelete[] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'reason' => "Has {$customer->active_orders_count} active orders",
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

        // Only delete customers without active orders
        $customersWithOrders = Customer::whereIn('id', $this->selected)
            ->whereHas('salesOrders', fn($q) => $q->whereNotIn('status', ['cancelled', 'delivered']))
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $customersWithOrders));

        if (empty($deletableIds)) {
            session()->flash('error', 'No customers can be deleted. All selected customers have active orders.');
            $this->cancelDelete();
            return;
        }

        $count = Customer::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} customers deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Customer::whereIn('id', $this->selected)
            ->update(['status' => 'active']);

        $this->clearSelection();
        session()->flash('success', "{$count} customers activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Customer::whereIn('id', $this->selected)
            ->update(['status' => 'inactive']);

        $this->clearSelection();
        session()->flash('success', "{$count} customers deactivated.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new CustomersExport(), 'customers-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new CustomersExport($this->selected), 'customers-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function getCustomersQuery()
    {
        return Customer::query()
            ->withCount('salesOrders as orders_count')
            ->withSum(['salesOrders' => fn($q) => $q->where('status', 'delivered')], 'total')
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('email', 'ilike', "%{$this->search}%")
                ->orWhere('phone', 'ilike', "%{$this->search}%"))
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
