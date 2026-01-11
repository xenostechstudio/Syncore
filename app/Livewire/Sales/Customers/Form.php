<?php

namespace App\Livewire\Sales\Customers;

use App\Livewire\Concerns\WithNotes;
use App\Models\Sales\PaymentTerm;
use App\Models\Sales\Pricelist;
use App\Models\Sales\Customer;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Customer')]
class Form extends Component
{
    use WithNotes;

    public ?int $customerId = null;
    
    // Customer fields
    public string $type = 'person';
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public string $notes = '';
    public ?int $salesperson_id = null;
    public ?int $payment_term_id = null;
    public string $payment_method = '';
    public ?int $pricelist_id = null;
    public string $banks = '';
    public string $status = 'active';

    // Timestamps
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->customerId = $id;
            $customer = Customer::findOrFail($id);
            
            $this->type = $customer->type ?? 'person';
            $this->name = $customer->name;
            $this->email = $customer->email ?? '';
            $this->phone = $customer->phone ?? '';
            $this->address = $customer->address ?? '';
            $this->city = $customer->city ?? '';
            $this->country = $customer->country ?? '';
            $this->notes = $customer->notes ?? '';
            $this->salesperson_id = $customer->salesperson_id;
            $this->payment_term_id = $customer->payment_term_id;
            $this->payment_method = $customer->payment_method ?? '';
            $this->pricelist_id = $customer->pricelist_id;
            $this->banks = $customer->banks ?? '';
            $this->status = $customer->status ?? 'active';
            $this->createdAt = $customer->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $customer->updated_at->format('M d, Y \a\t H:i');
        }
    }

    public function save(): void
    {
        $this->validate([
            'type' => 'required|in:person,company',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:5000',
            'salesperson_id' => 'nullable|exists:users,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'payment_method' => 'nullable|string|max:50',
            'pricelist_id' => 'nullable|exists:pricelists,id',
            'banks' => 'nullable|string|max:5000',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Please enter a customer name.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $data = [
            'type' => $this->type,
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'country' => $this->country ?: null,
            'notes' => $this->notes ?: null,
            'salesperson_id' => $this->salesperson_id,
            'payment_term_id' => $this->payment_term_id,
            'payment_method' => $this->payment_method ?: null,
            'pricelist_id' => $this->pricelist_id,
            'banks' => $this->banks ?: null,
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
        $salespeople = User::orderBy('name')->get();
        $paymentTerms = PaymentTerm::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $pricelists = Pricelist::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.sales.customers.form', [
            'salespeople' => $salespeople,
            'paymentTerms' => $paymentTerms,
            'pricelists' => $pricelists,
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
