<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create main admin user
        $admin = User::firstOrCreate(
            ['email' => 'rifqi@mail.com'],
            [
                'name' => 'Rifqi Muhammad Aziz',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        // Assign super-admin role
        if ($admin && Role::where('name', 'super-admin')->exists()) {
            $admin->syncRoles(['super-admin']);
        }

        // Create demo users with different roles
        $demoUsers = [
            ['email' => 'manager@example.com', 'name' => 'Manager User', 'role' => 'manager'],
            ['email' => 'sales@example.com', 'name' => 'Sales User', 'role' => 'sales'],
            ['email' => 'warehouse@example.com', 'name' => 'Warehouse User', 'role' => 'warehouse'],
            ['email' => 'accountant@example.com', 'name' => 'Accountant User', 'role' => 'accountant'],
            ['email' => 'hr@example.com', 'name' => 'HR Manager', 'role' => 'hr-manager'],
        ];

        foreach ($demoUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            if ($user && Role::where('name', $userData['role'])->exists()) {
                $user->syncRoles([$userData['role']]);
            }
        }
    }
}
