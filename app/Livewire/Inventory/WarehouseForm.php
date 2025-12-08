<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Create Warehouse')]
class WarehouseForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $location = null;

    #[Validate('nullable|string|max:255')]
    public ?string $contact_info = null;

    public ?Warehouse $warehouse = null;
    public bool $editing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->warehouse = Warehouse::findOrFail($id);
            $this->editing = true;
            $this->fill($this->warehouse->toArray());
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing && $this->warehouse) {
            $this->warehouse->update($validated);
            session()->flash('success', 'Warehouse updated successfully.');
        } else {
            Warehouse::create($validated);
            session()->flash('success', 'Warehouse created successfully.');
        }

        $this->redirect(route('inventory.warehouses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.warehouse-form');
    }
}
