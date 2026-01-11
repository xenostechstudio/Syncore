<?php

namespace App\Livewire\Sales\Configuration\PaymentTerms;

use App\Livewire\Concerns\WithNotes;
use App\Models\Sales\PaymentTerm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Payment Term')]
class Form extends Component
{
    use WithNotes;

    public ?int $paymentTermId = null;
    
    public string $name = '';
    public string $code = '';
    public int $days = 0;
    public string $description = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    public bool $showDeleteConfirm = false;

    protected function getNotableModel()
    {
        return $this->paymentTermId ? PaymentTerm::find($this->paymentTermId) : null;
    }

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
            $this->createdAt = $term->created_at?->format('M d, Y \a\t H:i');
            $this->updatedAt = $term->updated_at?->format('M d, Y \a\t H:i');
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
            $term = PaymentTerm::findOrFail($this->paymentTermId);
            $term->update($data);
            $this->updatedAt = $term->updated_at->format('M d, Y \a\t H:i');
            session()->flash('success', 'Payment term updated successfully.');
        } else {
            $term = PaymentTerm::create($data);
            $this->paymentTermId = $term->id;
            $this->createdAt = $term->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $term->updated_at->format('M d, Y \a\t H:i');
            session()->flash('success', 'Payment term created successfully.');
            $this->redirect(route('sales.configuration.payment-terms.edit', $term->id), navigate: true);
        }
    }

    public function confirmDelete(): void
    {
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
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
        return view('livewire.sales.configuration.payment-terms.form', [
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
