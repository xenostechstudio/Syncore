<?php

use App\Livewire\Sales\Configuration\PaymentTerms\Form as PaymentTermsForm;
use App\Livewire\Sales\Configuration\Pricelists\Form as PricelistsForm;
use App\Livewire\Sales\Configuration\Promotions\Form as PromotionsForm;
use App\Livewire\Sales\Configuration\Taxes\Form as TaxesForm;
use App\Livewire\Sales\Customers\Form as CustomerForm;
use App\Models\Sales\Customer;
use App\Models\Sales\PaymentTerm;
use App\Models\Sales\Pricelist;
use App\Models\Sales\Tax;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * B.1 hardening: the four Sales\Configuration\* Form components and the
 * Customer save() / rename path had no authorizePermission gate. The
 * route middleware only checked access.sales; any user with that gate
 * could rename customers, edit tax rates, change pricelists, or wipe
 * promotions via the Livewire endpoint regardless of their per-action
 * permissions. These tests pin the new gates.
 */
function actAsSalesViewer(): User
{
    foreach ([
        'access.sales',
        'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
        'access.customers',
        'customers.view', 'customers.create', 'customers.edit',
    ] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role = Role::findOrCreate('sales-viewer', 'web');
    $role->syncPermissions(['access.sales', 'sales.view', 'access.customers', 'customers.view']);

    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('PaymentTerms save (new) forbidden without sales.create', function () {
    actAsSalesViewer();
    Livewire::test(PaymentTermsForm::class)
        ->set('name', 'Net 999')
        ->set('code', 'NET999')
        ->set('days', 999)
        ->call('save')
        ->assertForbidden();

    expect(PaymentTerm::where('code', 'NET999')->exists())->toBeFalse();
});

it('PaymentTerms save (existing) forbidden without sales.edit', function () {
    actAsSalesViewer();
    $term = PaymentTerm::factory()->create(['name' => 'Original']);

    Livewire::test(PaymentTermsForm::class, ['id' => $term->id])
        ->set('name', 'Renamed')
        ->call('save')
        ->assertForbidden();

    expect($term->fresh()->name)->toBe('Original');
});

it('PaymentTerms delete forbidden without sales.delete', function () {
    actAsSalesViewer();
    $term = PaymentTerm::factory()->create();

    Livewire::test(PaymentTermsForm::class, ['id' => $term->id])
        ->call('delete')
        ->assertForbidden();

    expect(PaymentTerm::find($term->id))->not->toBeNull();
});

it('Taxes save forbidden without sales.edit', function () {
    actAsSalesViewer();
    $tax = Tax::factory()->create(['rate' => 11]);

    Livewire::test(TaxesForm::class, ['id' => $tax->id])
        ->set('rate', 99)
        ->call('save')
        ->assertForbidden();

    expect((float) $tax->fresh()->rate)->toBe(11.0);
});

it('Pricelists save forbidden without sales.edit', function () {
    actAsSalesViewer();
    $list = Pricelist::factory()->create(['discount' => 5]);

    Livewire::test(PricelistsForm::class, ['id' => $list->id])
        ->set('discount', 95)
        ->call('save')
        ->assertForbidden();

    expect((float) $list->fresh()->discount)->toBe(5.0);
});

it('Customer rename forbidden without customers.edit', function () {
    actAsSalesViewer();
    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Original Name',
        'country' => 'ID',
        'status' => 'active',
    ]);

    Livewire::test(CustomerForm::class, ['id' => $customer->id])
        ->set('name', 'Hijacked')
        ->call('save')
        ->assertForbidden();

    expect($customer->fresh()->name)->toBe('Original Name');
});

it('Customer creation forbidden without customers.create', function () {
    actAsSalesViewer();
    $unique = 'New '.uniqid();

    Livewire::test(CustomerForm::class)
        ->set('type', 'person')
        ->set('name', $unique)
        ->set('country', 'ID')
        ->call('save')
        ->assertForbidden();

    expect(Customer::where('name', $unique)->exists())->toBeFalse();
});

it('admin (super-admin) can save Sales Configuration', function () {
    actAsAdmin();

    Livewire::test(PaymentTermsForm::class)
        ->set('name', 'Net Test')
        ->set('code', 'NET-T-'.uniqid())
        ->set('days', 30)
        ->call('save')
        ->assertHasNoErrors();
});
