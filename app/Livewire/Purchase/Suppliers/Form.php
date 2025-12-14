<?php

namespace App\Livewire\Purchase\Suppliers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Supplier')]
class Form extends Component
{
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
    public array $activityLog = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $supplier = DB::table('suppliers')->where('id', $id)->first();

            if ($supplier) {
                $this->supplierId = $supplier->id;
                $this->name = $supplier->name;
                $this->contact_person = $supplier->contact_person ?? '';
                $this->email = $supplier->email ?? '';
                $this->phone = $supplier->phone ?? '';
                $this->address = $supplier->address ?? '';
                $this->city = $supplier->city ?? '';
                $this->country = $supplier->country ?? '';
                $this->is_active = $supplier->is_active;

                $this->createdAt = \Carbon\Carbon::parse($supplier->created_at)->format('M d, Y \a\t H:i');
                $this->updatedAt = \Carbon\Carbon::parse($supplier->updated_at)->format('M d, Y \a\t H:i');

                $this->activityLog = [
                    [
                        'type' => 'created',
                        'message' => 'Supplier created',
                        'user' => Auth::user()?->name ?? 'System',
                        'time' => $this->createdAt,
                    ],
                ];

                if ($supplier->updated_at > $supplier->created_at) {
                    $this->activityLog[] = [
                        'type' => 'updated',
                        'message' => 'Supplier updated',
                        'user' => Auth::user()?->name ?? 'System',
                        'time' => $this->updatedAt,
                    ];
                }
            }
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        if ($this->supplierId) {
            DB::table('suppliers')
                ->where('id', $this->supplierId)
                ->update([
                    'name' => $this->name,
                    'contact_person' => $this->contact_person,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'city' => $this->city,
                    'country' => $this->country,
                    'is_active' => $this->is_active,
                    'updated_at' => now(),
                ]);

            session()->flash('success', 'Supplier updated successfully.');
        } else {
            $id = DB::table('suppliers')->insertGetId([
                'name' => $this->name,
                'contact_person' => $this->contact_person,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'city' => $this->city,
                'country' => $this->country,
                'is_active' => $this->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('success', 'Supplier created successfully.');
            $this->redirect(route('purchase.suppliers.edit', $id), navigate: true);
        }
    }

    public function delete(): void
    {
        if (!$this->supplierId) {
            return;
        }

        DB::table('suppliers')->where('id', $this->supplierId)->delete();

        session()->flash('success', 'Supplier deleted successfully.');
        $this->redirect(route('purchase.suppliers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase.suppliers.form');
    }
}
