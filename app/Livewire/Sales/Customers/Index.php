<?php

namespace App\Livewire\Sales\Customers;

use App\Exports\CustomersExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Sales\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Customers')]
class Index extends Component
{
    use WithIndexComponent;

    public bool $filterActive = false;
    public bool $filterInactive = false;
    public bool $filterWithOrders = false;

    public array $visibleColumns = [
        'customer' => true,
        'contact' => true,
        'location' => true,
        'orders' => true,
        'total' => true,
        'status' => true,
    ];

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'filterActive', 'filterInactive', 'filterWithOrders', 'groupBy']);
        $this->resetPage();
        $this->clearSelection();
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $customers = Customer::whereIn('id', $this->selected)
            ->withCount(['salesOrders as active_orders_count' => fn ($q) => $q->whereNotIn('status', ['cancelled', 'delivered'])])
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($customers as $customer) {
            if ($customer->active_orders_count === 0) {
                $canDelete[] = ['id' => $customer->id, 'name' => $customer->name];
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

        $customersWithOrders = Customer::whereIn('id', $this->selected)
            ->whereHas('salesOrders', fn ($q) => $q->whereNotIn('status', ['cancelled', 'delivered']))
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

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Customer::whereIn('id', $this->selected)->update(['status' => 'active']);

        $this->clearSelection();
        session()->flash('success', "{$count} customers activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Customer::whereIn('id', $this->selected)->update(['status' => 'inactive']);

        $this->clearSelection();
        session()->flash('success', "{$count} customers deactivated.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'customers-' . now()->format('Y-m-d') . '.xlsx'
            : 'customers-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CustomersExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return Customer::query()
            ->withCount('salesOrders as orders_count')
            ->withSum(['salesOrders' => fn ($q) => $q->where('status', 'delivered')], 'total')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->filterActive, fn ($q) => $q->where('status', 'active'))
            ->when($this->filterInactive, fn ($q) => $q->where('status', 'inactive'))
            ->when($this->filterWithOrders, fn ($q) => $q->has('salesOrders'));
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }

    public function render()
    {
        $customers = $this->applySorting($this->getQuery())
            ->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.sales.customers.index', [
            'customers' => $customers,
        ]);
    }
}
