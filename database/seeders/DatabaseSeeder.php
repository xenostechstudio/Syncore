<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create main admin user first (before ModulePermissionSeeder assigns super-admin to first user)
        User::firstOrCreate(
            ['email' => 'rifqi@mail.com'],
            [
                'name' => 'Rifqi Muhammad Aziz',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ModulePermissionSeeder::class,
            UserSeeder::class,
            InventorySeeder::class,
            SalesSeeder::class,
            DeliverySeeder::class,
            SupplierSeeder::class,
            AccountingSeeder::class,
            CRMSeeder::class,
            HRSeeder::class,
        ]);
    }
}
