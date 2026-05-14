<?php

namespace App\Livewire\Purchase\Suppliers;

use App\Livewire\Concerns\WithNotes;
use App\Livewire\Concerns\WithPermissions;
use App\Models\Purchase\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Supplier')]
class Form extends Component
{
    use WithNotes, WithPermissions;

    public ?int $supplierId = null;
    public string $name = '';
    public string $contact_person = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public bool $is_active = true;

    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->supplierId ? Supplier::find($this->supplierId) : null;
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $supplier = Supplier::findOrFail($id);

            $this->supplierId = $supplier->id;
            $this->name = $supplier->name;
            $this->contact_person = $supplier->contact_person ?? '';
            $this->email = $supplier->email ?? '';
            $this->phone = $supplier->phone ?? '';
            $this->address = $supplier->address ?? '';
            $this->city = $supplier->city ?? '';
            $this->country = $supplier->country ?? '';
            $this->is_active = $supplier->is_active;

            $this->createdAt = $supplier->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $supplier->updated_at->format('M d, Y \a\t H:i');
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $data = [
            'name' => $this->name,
            'contact_person' => $this->contact_person ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'country' => $this->country ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->supplierId) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $supplier->update($data);
            session()->flash('success', 'Supplier updated successfully.');
        } else {
            $supplier = Supplier::create($data);
            session()->flash('success', 'Supplier created successfully.');
            $this->redirect(route('purchase.suppliers.edit', $supplier->id), navigate: true);
        }
    }

    /**
     * Archive (soft-delete) the supplier. Master data is never hard
     * "deleted" from the form — it's retired with Archive, which keeps
     * the row so historical purchase orders / bills still resolve, and
     * is recoverable from the Archived filter on the index. See
     * "Destructive actions" in CLAUDE.md.
     */
    public function archive(): void
    {
        $this->authorizePermission('purchase.delete');

        if (! $this->supplierId) {
            return;
        }

        $supplier = Supplier::findOrFail($this->supplierId);
        $supplier->archive();

        session()->flash('success', 'Supplier archived. Find and restore it via the Archived filter on the suppliers list.');
        $this->redirect(route('purchase.suppliers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase.suppliers.form', [
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
