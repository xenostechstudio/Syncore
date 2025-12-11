<?php

namespace App\Livewire\Sales\Configuration\PaymentTerms;

use App\Models\Sales\PaymentTerm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Payment Terms')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public array $selected = [];
    public bool $selectAll = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = PaymentTerm::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        PaymentTerm::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected payment terms deleted successfully.');
    }

    public function render()
    {
        $paymentTerms = PaymentTerm::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.sales.configuration.payment-terms.index', [
            'paymentTerms' => $paymentTerms,
        ]);
    }
}
