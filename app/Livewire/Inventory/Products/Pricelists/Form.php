<?php

namespace App\Livewire\Inventory\Products\Pricelists;

use App\Livewire\Concerns\WithNotes;
use App\Models\Sales\Pricelist;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Pricelist')]
class Form extends Component
{
    use WithNotes;

    public ?int $pricelistId = null;

    public string $name = '';
    public string $code = '';
    public string $currency = 'IDR';
    public string $type = 'percentage';
    public float $discount = 0;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public bool $is_active = true;
    public string $description = '';

    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    public bool $showDeleteConfirm = false;

    protected function getNotableModel()
    {
        return $this->pricelistId ? Pricelist::find($this->pricelistId) : null;
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->pricelistId = $id;
            $pricelist = Pricelist::findOrFail($id);

            $this->name = $pricelist->name;
            $this->code = $pricelist->code;
            $this->currency = $pricelist->currency;
            $this->type = $pricelist->type;
            $this->discount = (float) $pricelist->discount;
            $this->start_date = $pricelist->start_date?->format('Y-m-d');
            $this->end_date = $pricelist->end_date?->format('Y-m-d');
            $this->is_active = $pricelist->is_active;
            $this->description = $pricelist->description ?? '';
            $this->createdAt = $pricelist->created_at?->format('M d, Y \a\t H:i');
            $this->updatedAt = $pricelist->updated_at?->format('M d, Y \a\t H:i');
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:pricelists,code,' . $this->pricelistId,
            'currency' => 'required|string|size:3',
            'type' => 'required|in:percentage,fixed',
            'discount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'currency' => strtoupper($this->currency),
            'type' => $this->type,
            'discount' => $this->discount,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'is_active' => $this->is_active,
            'description' => $this->description ?: null,
        ];

        if ($this->pricelistId) {
            $pricelist = Pricelist::findOrFail($this->pricelistId);
            $pricelist->update($data);
            $this->updatedAt = $pricelist->updated_at->format('M d, Y \a\t H:i');
            session()->flash('success', 'Pricelist updated successfully.');
        } else {
            $pricelist = Pricelist::create($data);
            $this->pricelistId = $pricelist->id;
            $this->createdAt = $pricelist->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $pricelist->updated_at->format('M d, Y \a\t H:i');
            session()->flash('success', 'Pricelist created successfully.');
            $this->redirect(route('inventory.products.pricelists.edit', $pricelist->id), navigate: true);
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
        if ($this->pricelistId) {
            Pricelist::destroy($this->pricelistId);
            session()->flash('success', 'Pricelist deleted successfully.');
            $this->redirect(route('inventory.products.pricelists.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.inventory.products.pricelists.form', [
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
