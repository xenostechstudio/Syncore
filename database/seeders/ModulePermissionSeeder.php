<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'access.inventory' => 'Access Inventory module',
            'access.sales' => 'Access Sales module',
            'access.delivery' => 'Access Delivery module',
            'access.invoicing' => 'Access Invoicing module',
            'access.purchase' => 'Access Purchase module',
            'access.settings' => 'Access Settings module',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(array_keys($permissions));

        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole($adminRole->name)) {
            $firstUser->assignRole($adminRole);
        }
    }
}
