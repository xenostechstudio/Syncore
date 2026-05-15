<?php

/**
 * Customer is the pilot for the master-data hard-Delete gate (see
 * "Destructive actions" in CLAUDE.md). Hard Delete is a true
 * `forceDelete()` — distinct from the recoverable Archive — and is
 * allowed *only* when nothing references the customer. A customer with
 * orders or invoices must be Archived instead; forceDelete would
 * cascade-destroy those documents.
 */

use App\Livewire\Sales\Customers\Form;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Livewire\Livewire;

function makeCustomerForHardDelete(string $name): Customer
{
    return Customer::create([
        'type' => 'person',
        'name' => $name,
        'address' => 'addr',
        'country' => 'ID',
        'status' => 'active',
    ]);
}

it('hard-deletes an unreferenced customer (gone for good, not soft-deleted)', function () {
    actAsAdmin();
    $customer = makeCustomerForHardDelete('Deletable '.uniqid());

    Livewire::test(Form::class, ['id' => $customer->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('sales.customers.index'));

    expect(Customer::withTrashed()->find($customer->id))->toBeNull();
});

it('refuses to hard-delete a customer that has orders', function () {
    actAsAdmin();
    $customer = makeCustomerForHardDelete('Referenced '.uniqid());
    SalesOrder::factory()->create(['customer_id' => $customer->id]);

    Livewire::test(Form::class, ['id' => $customer->id])
        ->assertSet('canDelete', false)
        ->call('delete');

    // Still present — neither hard- nor soft-deleted.
    expect(Customer::find($customer->id))->not->toBeNull();
});
