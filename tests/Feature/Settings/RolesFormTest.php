<?php

use App\Livewire\Settings\Roles\Form;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
    $this->actingAs($this->user);
});

describe('Roles Form Component', function () {
    it('can render create form', function () {
        Livewire::test(Form::class)
            ->assertStatus(200)
            ->assertSet('roleId', null)
            ->assertSet('roleName', '');
    });

    it('can render edit form with existing role', function () {
        $role = Role::findByName('sales');

        Livewire::test(Form::class, ['id' => $role->id])
            ->assertStatus(200)
            ->assertSet('roleId', $role->id)
            ->assertSet('roleName', 'sales');
    });

    it('can create a new role', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'test-role')
            ->set('roleGuard', 'web')
            ->call('save')
            ->assertHasNoErrors();

        expect(Role::where('name', 'test-role')->exists())->toBeTrue();
    });

    it('validates role name is required', function () {
        Livewire::test(Form::class)
            ->set('roleName', '')
            ->call('save')
            ->assertHasErrors(['roleName' => 'required']);
    });

    it('can update existing role', function () {
        $role = Role::create(['name' => 'update-test', 'guard_name' => 'web']);

        Livewire::test(Form::class, ['id' => $role->id])
            ->set('roleName', 'updated-role')
            ->call('save')
            ->assertHasNoErrors();

        expect(Role::where('name', 'updated-role')->exists())->toBeTrue();
    });

    it('can delete a role', function () {
        $role = Role::create(['name' => 'delete-test', 'guard_name' => 'web']);

        Livewire::test(Form::class, ['id' => $role->id])
            ->call('delete');

        expect(Role::where('name', 'delete-test')->exists())->toBeFalse();
    });

    it('can set module access levels', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'access-test')
            ->set('moduleAccessLevels.sales', 'full')
            ->set('moduleAccessLevels.inventory', 'view')
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::findByName('access-test');
        $permissions = $role->permissions->pluck('name')->toArray();

        // Full access should include all sales permissions
        expect($permissions)->toContain('access.sales');
        expect($permissions)->toContain('sales.view');
        expect($permissions)->toContain('sales.create');
        expect($permissions)->toContain('sales.delete');

        // View only should only have view
        expect($permissions)->toContain('access.inventory');
        expect($permissions)->toContain('inventory.view');
        expect($permissions)->not->toContain('inventory.delete');
    });

    it('can grant full access to all modules', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'full-access-test')
            ->call('selectAllModuleAccess')
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::findByName('full-access-test');
        
        // Should have access to all modules
        expect($role->permissions->where('name', 'access.sales')->count())->toBe(1);
        expect($role->permissions->where('name', 'access.hr')->count())->toBe(1);
        expect($role->permissions->where('name', 'access.accounting')->count())->toBe(1);
    });

    it('can clear all permissions', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'clear-test')
            ->set('moduleAccessLevels.sales', 'full')
            ->call('deselectAll')
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::findByName('clear-test');
        expect($role->permissions->count())->toBe(0);
    });

    it('loads existing role permissions correctly', function () {
        $salesRole = Role::findByName('sales');

        $component = Livewire::test(Form::class, ['id' => $salesRole->id]);

        // Sales role should have edit level for sales module (has create/edit but not delete)
        expect($component->get('moduleAccessLevels.sales'))->toBe('edit');
    });

    it('can toggle individual permissions', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'toggle-test')
            ->call('togglePermission', 'access.sales')
            ->assertSet('selectedPermissions', ['access.sales'])
            ->call('togglePermission', 'access.sales')
            ->assertSet('selectedPermissions', []);
    });
});

describe('Roles Form Access Level Mapping', function () {
    it('view level grants only view permission', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'view-level-test')
            ->set('moduleAccessLevels.sales', 'view')
            ->call('save');

        $role = Role::findByName('view-level-test');
        $permissions = $role->permissions->pluck('name')->toArray();

        expect($permissions)->toContain('access.sales');
        expect($permissions)->toContain('sales.view');
        expect($permissions)->not->toContain('sales.create');
        expect($permissions)->not->toContain('sales.edit');
        expect($permissions)->not->toContain('sales.delete');
    });

    it('edit level grants view, create, and edit permissions', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'edit-level-test')
            ->set('moduleAccessLevels.sales', 'edit')
            ->call('save');

        $role = Role::findByName('edit-level-test');
        $permissions = $role->permissions->pluck('name')->toArray();

        expect($permissions)->toContain('access.sales');
        expect($permissions)->toContain('sales.view');
        expect($permissions)->toContain('sales.create');
        expect($permissions)->toContain('sales.edit');
        expect($permissions)->not->toContain('sales.delete');
    });

    it('full level grants all module permissions', function () {
        Livewire::test(Form::class)
            ->set('roleName', 'full-level-test')
            ->set('moduleAccessLevels.sales', 'full')
            ->call('save');

        $role = Role::findByName('full-level-test');
        $permissions = $role->permissions->pluck('name')->toArray();

        expect($permissions)->toContain('access.sales');
        expect($permissions)->toContain('sales.view');
        expect($permissions)->toContain('sales.create');
        expect($permissions)->toContain('sales.edit');
        expect($permissions)->toContain('sales.delete');
        expect($permissions)->toContain('sales.confirm');
        expect($permissions)->toContain('sales.cancel');
    });
});
