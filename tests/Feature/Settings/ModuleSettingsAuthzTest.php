<?php

use App\Livewire\Settings\Modules\Invoice as InvoiceModule;
use App\Livewire\Settings\Modules\PurchaseOrder as PurchaseOrderModule;
use App\Livewire\Settings\Modules\SalesOrder as SalesOrderModule;
use App\Models\Settings\PurchaseOrderSetting;
use App\Models\Settings\SalesOrderSetting;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Module settings (Sales Order, Purchase Order, Invoice) were route-only
 * gated. With `access.settings`, anyone could call save() and mutate
 * document numbering, default terms, payment gateway secrets, etc. These
 * tests pin the new settings.edit gate plus the try/finally spinner-reset
 * dispatch — same hardening as Company / Email / Localization saves.
 */
function actAsModulesViewer(): User
{
    foreach (['access.settings', 'settings.view', 'settings.edit'] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role = Role::findOrCreate('modules-viewer', 'web');
    $role->syncPermissions(['access.settings', 'settings.view']);

    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('SalesOrder save is forbidden without settings.edit', function () {
    actAsModulesViewer();
    $before = SalesOrderSetting::instance()->doc_number_prefix;

    Livewire::test(SalesOrderModule::class)
        ->set('doc_number_prefix', 'HACK')
        ->call('save')
        ->assertForbidden();

    SalesOrderSetting::clearCache();
    expect(SalesOrderSetting::instance()->fresh()->doc_number_prefix)->toBe($before);
});

it('PurchaseOrder save is forbidden without settings.edit', function () {
    actAsModulesViewer();
    $before = PurchaseOrderSetting::instance()->doc_number_prefix;

    Livewire::test(PurchaseOrderModule::class)
        ->set('doc_number_prefix', 'HACK')
        ->call('save')
        ->assertForbidden();

    PurchaseOrderSetting::clearCache();
    expect(PurchaseOrderSetting::instance()->fresh()->doc_number_prefix)->toBe($before);
});

it('Invoice save is forbidden without settings.edit', function () {
    actAsModulesViewer();

    Livewire::test(InvoiceModule::class)
        ->set('invoice_title', 'HACKED')
        ->call('save')
        ->assertForbidden();
});

it('SalesOrder save dispatches saved event even on validation failure', function () {
    actAsAdmin();

    Livewire::test(SalesOrderModule::class)
        ->set('doc_number_prefix', '') // required
        ->call('save')
        ->assertHasErrors(['doc_number_prefix'])
        ->assertDispatched('sales-order-saved');
});

it('PurchaseOrder save dispatches saved event even on validation failure', function () {
    actAsAdmin();

    Livewire::test(PurchaseOrderModule::class)
        ->set('doc_number_prefix', '') // required
        ->call('save')
        ->assertHasErrors(['doc_number_prefix'])
        ->assertDispatched('purchase-order-saved');
});

it('Invoice save still dispatches saved event on the happy path', function () {
    actAsAdmin();

    // Invoice's save() has no validate() rules today, so we only assert the
    // dispatch happens on success — the try/finally guarantees it would
    // also fire on a future DB-level throw.
    Livewire::test(InvoiceModule::class)
        ->set('invoice_title', 'TEST INVOICE '.uniqid())
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('invoice-saved');
});
