<?php

use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);
});

describe('ModulePermissionSeeder', function () {
    it('creates all expected permissions', function () {
        $expectedModules = [
            'sales', 'customers', 'invoicing', 'crm', 'purchase', 
            'inventory', 'delivery', 'hr', 'payroll', 'leave', 
            'accounting', 'reports', 'settings', 'users', 'roles', 'audit'
        ];

        foreach ($expectedModules as $module) {
            expect(Permission::where('name', "access.{$module}")->exists())->toBeTrue(
                "Missing access permission for module: {$module}"
            );
        }
    });

    it('creates all expected roles', function () {
        $expectedRoles = [
            'super-admin', 'admin', 'manager', 'sales', 
            'warehouse', 'accountant', 'hr-manager', 'employee'
        ];

        foreach ($expectedRoles as $roleName) {
            expect(Role::where('name', $roleName)->exists())->toBeTrue(
                "Missing role: {$roleName}"
            );
        }
    });

    it('grants super-admin all permissions', function () {
        $superAdmin = Role::findByName('super-admin');
        $totalPermissions = Permission::count();

        expect($superAdmin->permissions->count())->toBe($totalPermissions);
    });

    it('creates module-specific permissions', function () {
        $salesPermissions = Permission::where('name', 'like', 'sales.%')->get();
        
        expect($salesPermissions->pluck('name')->toArray())->toContain(
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.confirm',
            'sales.cancel'
        );
    });
});

describe('Role Permissions', function () {
    it('sales role has correct module access', function () {
        $salesRole = Role::findByName('sales');
        $permissions = $salesRole->permissions->pluck('name')->toArray();

        // Should have access
        expect($permissions)->toContain('access.sales');
        expect($permissions)->toContain('access.customers');
        expect($permissions)->toContain('sales.view');
        expect($permissions)->toContain('sales.create');

        // Should NOT have
        expect($permissions)->not->toContain('access.hr');
        expect($permissions)->not->toContain('access.payroll');
        expect($permissions)->not->toContain('sales.delete');
    });

    it('warehouse role has inventory permissions', function () {
        $warehouseRole = Role::findByName('warehouse');
        $permissions = $warehouseRole->permissions->pluck('name')->toArray();

        expect($permissions)->toContain('access.inventory');
        expect($permissions)->toContain('inventory.adjust');
        expect($permissions)->toContain('inventory.transfer');
        expect($permissions)->toContain('access.delivery');
    });

    it('accountant role has financial permissions', function () {
        $accountantRole = Role::findByName('accountant');
        $permissions = $accountantRole->permissions->pluck('name')->toArray();

        expect($permissions)->toContain('access.accounting');
        expect($permissions)->toContain('accounting.post');
        expect($permissions)->toContain('invoicing.record_payment');
        expect($permissions)->toContain('reports.financial');
    });

    it('employee role has limited permissions', function () {
        $employeeRole = Role::findByName('employee');
        $permissions = $employeeRole->permissions->pluck('name')->toArray();

        expect($permissions)->toContain('access.leave');
        expect($permissions)->toContain('leave.view');
        expect($permissions)->toContain('leave.create');
        
        // Should NOT have delete or approve
        expect($permissions)->not->toContain('leave.delete');
        expect($permissions)->not->toContain('leave.approve');
    });
});

describe('User Permission Assignment', function () {
    it('can assign role to user', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');

        expect($user->hasRole('sales'))->toBeTrue();
    });

    it('user inherits role permissions', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');

        expect($user->can('access.sales'))->toBeTrue();
        expect($user->can('sales.view'))->toBeTrue();
        expect($user->can('sales.create'))->toBeTrue();
    });

    it('user cannot access unauthorized modules', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');

        expect($user->can('access.sales'))->toBeFalse();
        expect($user->can('access.accounting'))->toBeFalse();
        expect($user->can('payroll.process'))->toBeFalse();
    });

    it('super-admin can access everything', function () {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        expect($user->can('access.sales'))->toBeTrue();
        expect($user->can('access.hr'))->toBeTrue();
        expect($user->can('accounting.close_period'))->toBeTrue();
        expect($user->can('roles.delete'))->toBeTrue();
    });

    it('user can have multiple roles', function () {
        $user = User::factory()->create();
        $user->assignRole(['sales', 'warehouse']);

        expect($user->hasRole('sales'))->toBeTrue();
        expect($user->hasRole('warehouse'))->toBeTrue();
        expect($user->can('access.sales'))->toBeTrue();
        expect($user->can('access.inventory'))->toBeTrue();
    });
});
