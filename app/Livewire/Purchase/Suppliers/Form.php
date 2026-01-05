<?php

namespace App\Livewire\Purchase\Suppliers;

use App\Livewire\Concerns\WithNotes;
use App\Models\Purchase\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Supplier')]
class Form extends Component
{
    use WithNotes;

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

    public function getActivitiesAndNotesProperty(): \Illuminate\Support\Collection
    {
        if (!$this->supplierId) {
            return collect();
        }

        $supplier = Supplier::find($this->supplierId);
        
        // Get activity logs
        $activities = Activity::where('subject_type', Supplier::class)
            ->where('subject_id', $this->supplierId)
            ->with('causer')
            ->get()
            ->map(function ($activity) {
                return [
                    'type' => 'activity',
                    'data' => $activity,
                    'created_at' => $activity->created_at,
                ];
            });

        // Get notes
        $notes = $supplier->notes()->with('user')->get()->map(function ($note) {
            return [
                'type' => 'note',
                'data' => $note,
                'created_at' => $note->created_at,
            ];
        });

        // Merge and sort by created_at descending
        return $activities->concat($notes)
            ->sortByDesc('created_at')
            ->take(30)
            ->values();
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

    public function delete(): void
    {
        if (!$this->supplierId) {
            return;
        }

        Supplier::destroy($this->supplierId);

        session()->flash('success', 'Supplier deleted successfully.');
        $this->redirect(route('purchase.suppliers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase.suppliers.form', [
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
