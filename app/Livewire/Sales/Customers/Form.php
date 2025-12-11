<?php

namespace App\Livewire\Sales\Customers;

use App\Models\Sales\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Customer')]
class Form extends Component
{
    public ?int $customerId = null;
    
    // Customer fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public string $status = 'active';
    
    // Activity log
    public array $activities = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->customerId = $id;
            $customer = Customer::findOrFail($id);
            
            $this->name = $customer->name;
            $this->email = $customer->email ?? '';
            $this->phone = $customer->phone ?? '';
            $this->address = $customer->address ?? '';
            $this->city = $customer->city ?? '';
            $this->country = $customer->country ?? '';
            $this->status = $customer->status ?? 'active';
            
            // Load activities (mock for now)
            $this->activities = [
                [
                    'user' => Auth::user(),
                    'action' => 'Customer created',
                    'created_at' => $customer->created_at,
                ],
            ];
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Please enter a customer name.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'country' => $this->country ?: null,
            'status' => $this->status,
        ];

        if ($this->customerId) {
            $customer = Customer::findOrFail($this->customerId);
            $customer->update($data);
            session()->flash('success', 'Customer updated successfully.');
        } else {
            $customer = Customer::create($data);
            session()->flash('success', 'Customer created successfully.');
            $this->redirect(route('sales.customers.edit', $customer->id), navigate: true);
        }
    }

    public function delete(): void
    {
        if ($this->customerId) {
            Customer::destroy($this->customerId);
            session()->flash('success', 'Customer deleted successfully.');
            $this->redirect(route('sales.customers.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.sales.customers.form');
    }
}
