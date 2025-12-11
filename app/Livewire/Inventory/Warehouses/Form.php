<?php

namespace App\Livewire\Inventory\Warehouses;

use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Warehouse')]
class Form extends Component
{
    public ?int $warehouseId = null;
    public ?Warehouse $warehouse = null;
    public bool $editing = false;

    public string $name = '';
    public string $location = '';
    public string $contact_info = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->warehouseId = $id;
            $this->editing = true;
            $this->loadWarehouse();
        }
    }

    protected function loadWarehouse(): void
    {
        $this->warehouse = Warehouse::findOrFail($this->warehouseId);

        $this->name = $this->warehouse->name;
        $this->location = $this->warehouse->location ?? '';
        $this->contact_info = $this->warehouse->contact_info ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:500',
            'contact_info' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'location' => $this->location ?: null,
            'contact_info' => $this->contact_info ?: null,
        ];

        if ($this->warehouseId) {
            $warehouse = Warehouse::findOrFail($this->warehouseId);
            $warehouse->update($data);
            session()->flash('success', 'Warehouse updated successfully.');
        } else {
            $warehouse = Warehouse::create($data);
            $this->warehouseId = $warehouse->id;
            session()->flash('success', 'Warehouse created successfully.');
            $this->redirect(route('inventory.warehouses.edit', $warehouse->id), navigate: true);
            return;
        }
    }

    public function delete(): void
    {
        if ($this->warehouseId) {
            Warehouse::destroy($this->warehouseId);
            session()->flash('success', 'Warehouse deleted successfully.');
            $this->redirect(route('inventory.warehouses.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.inventory.warehouses.form');
    }
}
