<?php

namespace App\Livewire\Delivery\Orders;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Delivery\DeliveryOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Delivery'])]
#[Title('Delivery Orders')]
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
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;
    
    public array $visibleColumns = [
        'delivery_number' => true,
        'sales_order' => true,
        'recipient' => true,
        'courier' => true,
        'delivery_date' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        if (! in_array($view, ['list', 'grid'])) {
            return;
        }

        $this->view = $view;
    }

    public function toggleColumn(string $column): void
    {
        $this->visibleColumns[$column] = !($this->visibleColumns[$column] ?? true);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        if (count($this->selected) > 0) {
            \App\Models\Delivery\DeliveryOrder::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Selected delivery orders deleted successfully.');
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

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getDeliveriesQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    private function getDeliveriesQuery()
    {
        return DeliveryOrder::query()
            ->with(['salesOrder.customer', 'warehouse'])
            ->when($this->search, fn($q) => $q->where(fn ($qq) => $qq
                ->where('delivery_number', 'like', "%{$this->search}%")
                ->orWhere('tracking_number', 'like', "%{$this->search}%")
                ->orWhereHas('salesOrder.customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'delivery_date', fn($q) => $q->orderBy('delivery_date'));
    }

    public function render()
    {
        $deliveries = $this->getDeliveriesQuery()->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.delivery.orders.index', [
            'deliveries' => $deliveries,
        ]);
    }
}
