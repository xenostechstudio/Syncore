<?php

namespace App\Livewire\Sales\Configuration\PaymentTerms;

use App\Models\Sales\PaymentTerm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Payment Term')]
class Form extends Component
{
    public ?int $paymentTermId = null;
    
    public string $name = '';
    public string $code = '';
    public int $days = 0;
    public string $description = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->paymentTermId = $id;
            $term = PaymentTerm::findOrFail($id);
            
            $this->name = $term->name;
            $this->code = $term->code;
            $this->days = $term->days;
            $this->description = $term->description ?? '';
            $this->is_active = $term->is_active;
            $this->sort_order = $term->sort_order;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_terms,code,' . $this->paymentTermId,
            'days' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'days' => $this->days,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->paymentTermId) {
            PaymentTerm::findOrFail($this->paymentTermId)->update($data);
            session()->flash('success', 'Payment term updated successfully.');
        } else {
            $term = PaymentTerm::create($data);
            session()->flash('success', 'Payment term created successfully.');
            $this->redirect(route('sales.configuration.payment-terms.edit', $term->id), navigate: true);
        }
    }

    public function delete(): void
    {
        if ($this->paymentTermId) {
            PaymentTerm::destroy($this->paymentTermId);
            session()->flash('success', 'Payment term deleted successfully.');
            $this->redirect(route('sales.configuration.payment-terms.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.sales.configuration.payment-terms.form');
    }
}
