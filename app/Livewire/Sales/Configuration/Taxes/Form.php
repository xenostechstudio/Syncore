<?php

namespace App\Livewire\Sales\Configuration\Taxes;

use App\Models\Sales\Tax;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Tax')]
class Form extends Component
{
    public ?int $taxId = null;
    
    public string $name = '';
    public string $code = '';
    public float $rate = 0;
    public string $type = 'percentage';
    public string $scope = 'both';
    public bool $is_active = true;
    public bool $include_in_price = false;
    public string $description = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->taxId = $id;
            $tax = Tax::findOrFail($id);
            
            $this->name = $tax->name;
            $this->code = $tax->code;
            $this->rate = $tax->rate;
            $this->type = $tax->type;
            $this->scope = $tax->scope;
            $this->is_active = $tax->is_active;
            $this->include_in_price = $tax->include_in_price;
            $this->description = $tax->description ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:taxes,code,' . $this->taxId,
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'scope' => 'required|in:sales,purchase,both',
        ]);

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'rate' => $this->rate,
            'type' => $this->type,
            'scope' => $this->scope,
            'is_active' => $this->is_active,
            'include_in_price' => $this->include_in_price,
            'description' => $this->description ?: null,
        ];

        if ($this->taxId) {
            Tax::findOrFail($this->taxId)->update($data);
            session()->flash('success', 'Tax updated successfully.');
        } else {
            $tax = Tax::create($data);
            session()->flash('success', 'Tax created successfully.');
            $this->redirect(route('sales.configuration.taxes.edit', $tax->id), navigate: true);
        }
    }

    public function delete(): void
    {
        if ($this->taxId) {
            Tax::destroy($this->taxId);
            session()->flash('success', 'Tax deleted successfully.');
            $this->redirect(route('sales.configuration.taxes.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.sales.configuration.taxes.form');
    }
}
