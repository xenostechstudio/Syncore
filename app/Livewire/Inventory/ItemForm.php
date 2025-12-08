<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Create Item')]
class ItemForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:50|unique:inventory_items,sku')]
    public string $sku = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('required|integer|min:0')]
    public int $quantity = 0;

    #[Validate('nullable|numeric|min:0')]
    public ?float $cost_price = null;

    #[Validate('nullable|numeric|min:0')]
    public ?float $selling_price = null;

    #[Validate('required|in:in_stock,low_stock,out_of_stock')]
    public string $status = 'in_stock';

    public ?InventoryItem $item = null;
    public bool $editing = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->item = InventoryItem::findOrFail($id);
            $this->editing = true;
            $this->fill($this->item->toArray());
        }
    }

    public function generateSku(): void
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $this->name), 0, 3));
        $random = strtoupper(substr(uniqid(), -4));
        $this->sku = $prefix . '-' . $random;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Auto-set status based on quantity
        if ($validated['quantity'] === 0) {
            $validated['status'] = 'out_of_stock';
        } elseif ($validated['quantity'] < 10) {
            $validated['status'] = 'low_stock';
        } else {
            $validated['status'] = 'in_stock';
        }

        if ($this->editing && $this->item) {
            $this->item->update($validated);
            session()->flash('success', 'Item updated successfully.');
        } else {
            InventoryItem::create($validated);
            session()->flash('success', 'Item created successfully.');
        }

        $this->redirect(route('inventory.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.item-form', [
            'warehouses' => Warehouse::all(),
        ]);
    }
}
