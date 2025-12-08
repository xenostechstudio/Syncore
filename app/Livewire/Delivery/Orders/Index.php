<?php

namespace App\Livewire\Delivery\Orders;

use App\Models\Delivery\DeliveryOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Delivery'])]
#[Title('Delivery Orders')]
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

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
    }

    public function render()
    {
        $deliveries = DeliveryOrder::query()
            ->with(['salesOrder.customer', 'warehouse'])
            ->when($this->search, fn($q) => $q->where('delivery_number', 'like', "%{$this->search}%")
                ->orWhere('tracking_number', 'like', "%{$this->search}%")
                ->orWhereHas('salesOrder.customer', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'delivery_date', fn($q) => $q->orderBy('delivery_date'))
            ->paginate(12);

        return view('livewire.delivery.orders.index', [
            'deliveries' => $deliveries,
        ]);
    }
}
