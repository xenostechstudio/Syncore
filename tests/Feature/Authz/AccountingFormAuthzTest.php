<?php

use App\Livewire\Accounting\Accounts\Form as AccountsForm;
use App\Livewire\Accounting\JournalEntries\Form as JournalEntriesForm;
use App\Models\Accounting\Account;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * B.1 hardening: Accounting forms (Accounts, JournalEntries) had no
 * authorizePermission gate. The route only checked access.accounting.
 * Anyone with that gate could create chart-of-accounts rows or post
 * journal entries via the Livewire endpoint. These tests pin the new
 * accounting.create / accounting.edit / accounting.delete gates.
 */
function actAsAccountingViewer(): User
{
    foreach (['access.accounting', 'accounting.view', 'accounting.create', 'accounting.edit', 'accounting.delete'] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role = Role::findOrCreate('accounting-viewer', 'web');
    $role->syncPermissions(['access.accounting', 'accounting.view']);

    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('Accounts save (new) forbidden without accounting.create', function () {
    actAsAccountingViewer();

    Livewire::test(AccountsForm::class)
        ->set('code', '9999')
        ->set('name', 'Imposter Account')
        ->set('accountType', 'asset')
        ->call('save')
        ->assertForbidden();

    expect(Account::where('code', '9999')->exists())->toBeFalse();
});

it('Accounts save (existing) forbidden without accounting.edit', function () {
    actAsAccountingViewer();
    $account = Account::factory()->create(['name' => 'Original Account']);

    Livewire::test(AccountsForm::class, ['id' => $account->id])
        ->set('name', 'Hijacked')
        ->call('save')
        ->assertForbidden();

    expect($account->fresh()->name)->toBe('Original Account');
});

it('Accounts delete forbidden without accounting.delete', function () {
    actAsAccountingViewer();
    $account = Account::factory()->create();

    Livewire::test(AccountsForm::class, ['id' => $account->id])
        ->call('delete')
        ->assertForbidden();

    expect(Account::find($account->id))->not->toBeNull();
});

it('JournalEntries save (new) forbidden without accounting.create', function () {
    actAsAccountingViewer();

    Livewire::test(JournalEntriesForm::class)
        ->set('entryDate', '2026-01-15')
        ->set('description', 'Test entry')
        ->call('save')
        ->assertForbidden();
});
