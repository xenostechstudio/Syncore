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
        $orders = SalesOrder::query()
            ->with('customer')
            ->when($this->search, fn($q) => $q->where('order_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'total_high', fn($q) => $q->orderByDesc('total'))
            ->when($this->sort === 'total_low', fn($q) => $q->orderBy('total'))
            ->paginate(12);

        return view('livewire.sales.orders.index', [
            'orders' => $orders,
        ]);
    }
}
