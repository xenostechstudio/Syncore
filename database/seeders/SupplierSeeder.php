<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Orion Components',
                'contact_person' => 'Liam Carter',
                'email' => 'hello@orioncomponents.com',
                'phone' => '+1-555-210-3344',
                'address' => '4822 Crescent Ave, Floor 8',
                'city' => 'San Francisco',
                'country' => 'USA',
                'is_active' => true,
            ],
            [
                'name' => 'Nexus Industrial Supply',
                'contact_person' => 'Sofia Martins',
                'email' => 'orders@nexusindustrial.io',
                'phone' => '+44 20 7946 2111',
                'address' => '89 Mercer Street',
                'city' => 'London',
                'country' => 'United Kingdom',
                'is_active' => true,
            ],
            [
                'name' => 'Pacific Precision Parts',
                'contact_person' => 'Ethan Park',
                'email' => 'sales@pacificprecision.parts',
                'phone' => '+62 21 7788 9821',
                'address' => 'Jl. Kemang Raya No. 19',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'is_active' => true,
            ],
            [
                'name' => 'Aurora Plastics Co.',
                'contact_person' => 'Laura Jensen',
                'email' => 'contact@auroraplastics.co',
                'phone' => '+61 2 9012 4455',
                'address' => '17 Wentworth Rd.',
                'city' => 'Sydney',
                'country' => 'Australia',
                'is_active' => true,
            ],
            [
                'name' => 'Vertex Metals & Alloy',
                'contact_person' => 'Marcus Ndlovu',
                'email' => 'support@vertexmetals.africa',
                'phone' => '+27 11 555 9082',
                'address' => '129 Rivonia Blvd',
                'city' => 'Johannesburg',
                'country' => 'South Africa',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->updateOrInsert(
                ['email' => $supplier['email']],
                array_merge($supplier, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
