<?php

/**
 * Customer is the pilot for the master-data Archive pattern (see
 * "Destructive actions" in CLAUDE.md). The form's destructive action is
 * Archive — a soft delete — and the index exposes a `filterArchived`
 * toggle plus `bulkRestore`, so an archived customer is recoverable
 * rather than vanishing into a black hole.
 */

use App\Livewire\Sales\Customers\Form;
use App\Livewire\Sales\Customers\Index;
use App\Models\Sales\Customer;
use Livewire\Livewire;

function makeCustomerRecord(string $name): Customer
{
    return Customer::create([
        'type' => 'person',
        'name' => $name,
        'address' => 'addr',
        'country' => 'ID',
        'status' => 'active',
    ]);
}

it('archives a customer as a recoverable soft delete (not a hard delete)', function () {
    actAsAdmin();
    $customer = makeCustomerRecord('Acme '.uniqid());

    Livewire::test(Form::class, ['id' => $customer->id])
        ->call('archive')
        ->assertRedirect(route('sales.customers.index'));

    // Soft-deleted: gone from default scope, still present withTrashed.
    expect(Customer::find($customer->id))->toBeNull();
    expect(Customer::withTrashed()->find($customer->id))->not->toBeNull();
    expect(Customer::withTrashed()->find($customer->id)->trashed())->toBeTrue();
});

it('hides archived customers from the index by default, shows them under the Archived filter', function () {
    actAsAdmin();
    $live = makeCustomerRecord('Live Customer '.uniqid());
    $archived = makeCustomerRecord('Archived Customer '.uniqid());
    $archived->delete();

    // Default: live shown, archived hidden.
    Livewire::test(Index::class)
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    // filterArchived on: only archived shown.
    Livewire::test(Index::class)
        ->set('filterArchived', true)
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('restores archived customers via bulkRestore', function () {
    actAsAdmin();
    $a = makeCustomerRecord('Restore A '.uniqid());
    $b = makeCustomerRecord('Restore B '.uniqid());
    $a->delete();
    $b->delete();

    expect(Customer::whereIn('id', [$a->id, $b->id])->count())->toBe(0);

    Livewire::test(Index::class)
        ->set('filterArchived', true)
        ->set('selected', [$a->id, $b->id])
        ->call('bulkRestore');

    // Both back in the default (non-trashed) scope.
    expect(Customer::whereIn('id', [$a->id, $b->id])->count())->toBe(2);
});
