<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModulePermissionSeeder extends Seeder
{
    /**
     * Module-based permissions structure.
     */
    protected array $modules = [
        'sales' => ['view', 'create', 'edit', 'delete', 'export', 'confirm', 'cancel'],
        'customers' => ['view', 'create', 'edit', 'delete', 'export'],
        'invoicing' => ['view', 'create', 'edit', 'delete', 'export', 'send', 'record_payment'],
        'crm' => ['view', 'create', 'edit', 'delete', 'export', 'convert_lead'],
        'purchase' => ['view', 'create', 'edit', 'delete', 'export', 'confirm', 'receive'],
        'inventory' => ['view', 'create', 'edit', 'delete', 'export', 'adjust', 'transfer'],
        'delivery' => ['view', 'create', 'edit', 'delete', 'export', 'confirm', 'complete'],
        'hr' => ['view', 'create', 'edit', 'delete', 'export'],
        'payroll' => ['view', 'create', 'edit', 'delete', 'export', 'process', 'approve'],
        'leave' => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
        'accounting' => ['view', 'create', 'edit', 'delete', 'export', 'post', 'close_period'],
        'reports' => ['view', 'export', 'sales', 'inventory', 'financial'],
        'settings' => ['view', 'edit'],
        'users' => ['view', 'create', 'edit', 'delete', 'assign_roles'],
        'roles' => ['view', 'create', 'edit', 'delete'],
        'audit' => ['view', 'export'],
    ];

    /**
     * Default roles configuration.
     */
    protected array $roles = [
        'super-admin' => '*',
        'admin' => ['exclude' => ['roles.delete', 'audit.export']],
        'manager' => [
            'sales' => ['view', 'create', 'edit', 'confirm', 'export'],
            'customers' => ['view', 'create', 'edit', 'export'],
            'invoicing' => ['view', 'create', 'edit', 'send', 'record_payment', 'export'],
            'crm' => ['view', 'create', 'edit', 'convert_lead', 'export'],
            'purchase' => ['view', 'create', 'edit', 'confirm', 'export'],
            'inventory' => ['view', 'create', 'edit', 'adjust', 'transfer', 'export'],
            'delivery' => ['view', 'create', 'edit', 'confirm', 'complete', 'export'],
            'hr' => ['view', 'export'],
            'leave' => ['view', 'approve', 'reject'],
            'reports' => ['view', 'export', 'sales', 'inventory'],
            'settings' => ['view'],
        ],
        'sales' => [
            'sales' => ['view', 'create', 'edit', 'confirm'],
            'customers' => ['view', 'create', 'edit'],
            'invoicing' => ['view', 'create', 'send'],
            'crm' => ['view', 'create', 'edit', 'convert_lead'],
            'inventory' => ['view'],
            'delivery' => ['view'],
            'reports' => ['view', 'sales'],
        ],
        'warehouse' => [
            'inventory' => ['view', 'create', 'edit', 'adjust', 'transfer'],
            'delivery' => ['view', 'create', 'edit', 'confirm', 'complete'],
            'purchase' => ['view', 'receive'],
            'reports' => ['view', 'inventory'],
        ],
        'accountant' => [
            'invoicing' => ['view', 'create', 'edit', 'record_payment', 'export'],
            'accounting' => ['view', 'create', 'edit', 'post', 'export'],
            'purchase' => ['view', 'export'],
            'reports' => ['view', 'export', 'financial'],
            'customers' => ['view'],
            'sales' => ['view', 'export'],
        ],
        'hr-manager' => [
            'hr' => ['view', 'create', 'edit', 'delete', 'export'],
            'payroll' => ['view', 'create', 'edit', 'process', 'approve', 'export'],
            'leave' => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
            'reports' => ['view'],
        ],
        'employee' => [
            'leave' => ['view', 'create'],
            'hr' => ['view'],
        ],
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();
        $this->createRoles();

        // Assign super-admin to first user
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole('super-admin')) {
            $firstUser->assignRole('super-admin');
        }
    }

    protected function createPermissions(): void
    {
        foreach ($this->modules as $module => $actions) {
            Permission::firstOrCreate(['name' => "access.{$module}", 'guard_name' => 'web']);
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
    }

    protected function createRoles(): void
    {
        $allPermissions = Permission::pluck('name')->toArray();

        foreach ($this->roles as $roleName => $config) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $permissions = [];

            if ($config === '*') {
                $permissions = $allPermissions;
            } elseif (isset($config['exclude'])) {
                $permissions = array_diff($allPermissions, $config['exclude']);
            } else {
                foreach ($config as $module => $actions) {
                    $permissions[] = "access.{$module}";
                    foreach ($actions as $action) {
                        $permissions[] = "{$module}.{$action}";
                    }
                }
            }

            $role->syncPermissions($permissions);
        }
    }
}
