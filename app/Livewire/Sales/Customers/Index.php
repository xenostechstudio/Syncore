<?php

namespace App\Livewire\Sales\Customers;

use App\Models\Sales\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Customers')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
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
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('status', 'delivered')], 'total')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        return view('livewire.sales.customers.index', [
            'customers' => $customers,
        ]);
    }
}
