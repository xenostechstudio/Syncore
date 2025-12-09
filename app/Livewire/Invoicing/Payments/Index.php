<?php

namespace App\Livewire\Invoicing\Payments;

use App\Models\Invoicing\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Payments')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $payments = Payment::query()
            ->with(['invoice.customer'])
            ->when($this->search, fn($q) => $q->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('invoice', fn($q) => $q->where('invoice_number', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.invoicing.payments.index', [
            'payments' => $payments,
        ]);
    }
}
