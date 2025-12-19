<?php

namespace App\Livewire\Invoicing\Payments;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Invoicing\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Payments')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $view = 'list';

    public function setView(string $view): void
    {
        if (! in_array($view, ['list'], true)) {
            return;
        }

        $this->view = $view;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $payments = Payment::query()
            ->with(['invoice.customer'])
            ->when($this->search, fn($q) => $q->where(fn ($qq) => $qq
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('invoice', fn($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ))
            ->latest()
            ->paginate(15, ['*'], 'page', $this->page);

        $this->totalPages = $payments->lastPage();

        return view('livewire.invoicing.payments.index', [
            'payments' => $payments,
        ]);
    }
}
