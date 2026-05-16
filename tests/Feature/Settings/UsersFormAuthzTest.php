<?php

use App\Livewire\Settings\Users\Form;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * The Users form was gated only by the route-level `access.settings`
 * middleware. Anyone with that permission (e.g. the Manager role, which
 * has settings.view) could call save() or delete() via the Livewire
 * endpoint and mutate any user record. These tests pin the new gates:
 * save → users.create / users.edit, delete → users.delete, role sync →
 * users.assign_roles.
 */
function actAsUsersViewer(): User
{
    foreach (['access.settings', 'settings.view', 'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign_roles'] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role = Role::findOrCreate('users-viewer', 'web');
    $role->syncPermissions(['access.settings', 'settings.view', 'users.view']);

    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('save for a new user is forbidden without users.create', function () {
    actAsUsersViewer();

    Livewire::test(Form::class)
        ->set('name', 'Imposter')
        ->set('email', 'imposter-'.uniqid().'@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertForbidden();

    expect(User::where('name', 'Imposter')->exists())->toBeFalse();
});

it('save for an existing user is forbidden without users.edit', function () {
    actAsUsersViewer();
    $target = User::factory()->create(['name' => 'Original']);

    Livewire::test(Form::class, ['id' => $target->id])
        ->set('name', 'Hijacked')
        ->call('save')
        ->assertForbidden();

    expect($target->fresh()->name)->toBe('Original');
});

it('delete is forbidden without users.delete', function () {
    actAsUsersViewer();
    $target = User::factory()->create();

    Livewire::test(Form::class, ['id' => $target->id])
        ->call('delete')
        ->assertForbidden();

    expect(User::find($target->id))->not->toBeNull();
});

it('save for a new user is allowed with users.create', function () {
    actAsAdmin();

    $email = 'fresh-'.uniqid().'@example.com';
    Livewire::test(Form::class)
        ->set('name', 'Fresh User')
        ->set('email', $email)
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasNoErrors();

    expect(User::where('email', $email)->exists())->toBeTrue();
});

it('role sync is skipped for an actor without users.assign_roles', function () {
    // Build an actor who can edit users but not assign roles.
    foreach (['access.settings', 'settings.view', 'users.view', 'users.edit', 'users.assign_roles'] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }
    $editorRole = Role::findOrCreate('user-editor', 'web');
    $editorRole->syncPermissions(['access.settings', 'settings.view', 'users.view', 'users.edit']);

    $actor = User::factory()->create();
    $actor->assignRole($editorRole);
    $this->actingAs($actor);

    // Target already has 'user-editor'; the editor tries to swap to another role.
    $otherRole = Role::findOrCreate('other-role', 'web');
    $target = User::factory()->create();
    $target->assignRole($editorRole);

    Livewire::test(Form::class, ['id' => $target->id])
        ->set('name', 'Renamed By Editor')
        ->set('selectedRole', 'other-role')
        ->call('save')
        ->assertHasNoErrors();

    $target->refresh();
    // Name change went through (users.edit).
    expect($target->name)->toBe('Renamed By Editor');
    // Role assignment was silently dropped (no users.assign_roles).
    expect($target->roles->pluck('name')->toArray())->toBe(['user-editor']);
});
