<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AccessLevelPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'sales',
            'purchase',
            'inventory',
            'invoicing',
            'delivery',
            'settings',
        ];

        $levels = ['view', 'full'];

        foreach ($modules as $module) {
            // Create module access permission (basic entry)
            Permission::firstOrCreate(
                ['name' => "access.{$module}", 'guard_name' => 'web']
            );

            // Create level-based permissions
            foreach ($levels as $level) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$level}", 'guard_name' => 'web']
                );
            }
        }
    }
}
