<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'rifqi@mail.com'],
            [
                'name' => 'Rifqi Muhammad Aziz',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        if ($user && Role::where('name', 'Administrator')->exists()) {
            $user->assignRole('Administrator');
        }
    }
}
