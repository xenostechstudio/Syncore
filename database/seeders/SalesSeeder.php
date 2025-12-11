<?php

namespace Database\Seeders;

use App\Models\Inventory\InventoryItem;
use App\Models\Sales\Customer;
use App\Models\Sales\PaymentTerm;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Sales\SalesTeam;
use App\Models\Sales\Tax;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Indonesian-style taxes
        $taxes = [
            [
                'name' => 'PPN 11%',
                'code' => 'PPN11',
                'rate' => 11.0,
                'type' => 'percentage',
                'scope' => 'sales',
                'is_active' => true,
                'include_in_price' => false,
                'description' => 'Pajak Pertambahan Nilai 11% sesuai peraturan Indonesia.',
            ],
            [
                'name' => 'PPN 11% (Termasuk Harga)',
                'code' => 'PPN11_INC',
                'rate' => 11.0,
                'type' => 'percentage',
                'scope' => 'sales',
                'is_active' => true,
                'include_in_price' => true,
                'description' => 'PPN 11% sudah termasuk dalam harga jual.',
            ],
            [
                'name' => 'PPN 0% (Ekspor)',
                'code' => 'PPN0',
                'rate' => 0.0,
                'type' => 'percentage',
                'scope' => 'sales',
                'is_active' => true,
                'include_in_price' => false,
                'description' => 'PPN 0% untuk transaksi ekspor dan tertentu.',
            ],
            [
                'name' => 'PPh 23 Jasa 2%',
                'code' => 'PPH23_2',
                'rate' => 2.0,
                'type' => 'percentage',
                'scope' => 'purchase',
                'is_active' => true,
                'include_in_price' => false,
                'description' => 'PPh 23 atas jasa dengan tarif 2%.',
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(
                ['code' => $tax['code']],
                $tax,
            );
        }

        // Seed common payment terms
        $paymentTerms = [
            [
                'name' => 'Immediate Payment',
                'code' => 'IMMEDIATE',
                'days' => 0,
                'description' => 'Pembayaran segera saat invoice diterbitkan.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Net 15 Days',
                'code' => 'NET15',
                'days' => 15,
                'description' => 'Jatuh tempo 15 hari setelah invoice.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Net 30 Days',
                'code' => 'NET30',
                'days' => 30,
                'description' => 'Jatuh tempo 30 hari setelah invoice.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Net 45 Days',
                'code' => 'NET45',
                'days' => 45,
                'description' => 'Jatuh tempo 45 hari setelah invoice.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Net 60 Days',
                'code' => 'NET60',
                'days' => 60,
                'description' => 'Jatuh tempo 60 hari setelah invoice.',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($paymentTerms as $term) {
            PaymentTerm::firstOrCreate(
                ['code' => $term['code']],
                $term,
            );
        }

        // Seed sales users & teams
        $salesUsersData = [
            ['name' => 'Sales Manager', 'email' => 'sales.manager@example.com'],
            ['name' => 'Account Executive 1', 'email' => 'ae1@example.com'],
            ['name' => 'Account Executive 2', 'email' => 'ae2@example.com'],
        ];

        $salesUsers = collect();

        foreach ($salesUsersData as $data) {
            $salesUsers->push(
                User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => 'password',
                        'email_verified_at' => now(),
                    ],
                ),
            );
        }

        $teams = [
            [
                'name' => 'Domestic Sales',
                'description' => 'Tim penjualan domestik Indonesia.',
                'leader_email' => 'sales.manager@example.com',
                'target_amount' => 500_000_000,
                'members' => ['sales.manager@example.com', 'ae1@example.com', 'ae2@example.com'],
            ],
            [
                'name' => 'Enterprise Sales',
                'description' => 'Tim penjualan enterprise dan B2B.',
                'leader_email' => 'ae1@example.com',
                'target_amount' => 750_000_000,
                'members' => ['ae1@example.com', 'ae2@example.com'],
            ],
        ];

        foreach ($teams as $teamData) {
            $leader = $salesUsers->firstWhere('email', $teamData['leader_email']);

            $team = SalesTeam::firstOrCreate(
                ['name' => $teamData['name']],
                [
                    'description' => $teamData['description'],
                    'leader_id' => $leader?->id,
                    'target_amount' => $teamData['target_amount'],
                    'is_active' => true,
                ],
            );

            $memberIds = $salesUsers
                ->whereIn('email', $teamData['members'])
                ->pluck('id')
                ->all();

            if (!empty($memberIds)) {
                $team->members()->syncWithoutDetaching($memberIds);
            }
        }

        // Create customers
        $customers = [
            ['name' => 'PT Maju Bersama', 'email' => 'contact@majubersama.co.id', 'phone' => '+62 21 5551234', 'address' => 'Jl. Sudirman No. 123', 'city' => 'Jakarta', 'status' => 'active'],
            ['name' => 'CV Teknologi Nusantara', 'email' => 'info@teknusa.com', 'phone' => '+62 21 5555678', 'address' => 'Jl. Gatot Subroto No. 45', 'city' => 'Jakarta', 'status' => 'active'],
            ['name' => 'Toko Elektronik Jaya', 'email' => 'sales@ejaya.com', 'phone' => '+62 22 4441234', 'address' => 'Jl. Asia Afrika No. 78', 'city' => 'Bandung', 'status' => 'active'],
            ['name' => 'PT Digital Prima', 'email' => 'order@digitalprima.id', 'phone' => '+62 31 8881234', 'address' => 'Jl. Pemuda No. 56', 'city' => 'Surabaya', 'status' => 'active'],
            ['name' => 'CV Solusi Kreatif', 'email' => 'hello@solusikreatif.com', 'phone' => '+62 24 7771234', 'address' => 'Jl. Pandanaran No. 89', 'city' => 'Semarang', 'status' => 'active'],
            ['name' => 'Toko Komputer Mega', 'email' => 'mega@komputer.com', 'phone' => '+62 61 4561234', 'address' => 'Jl. Imam Bonjol No. 12', 'city' => 'Medan', 'status' => 'inactive'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Get items and user for orders
        $items = InventoryItem::all();
        $user = User::first();
        
        if ($items->isEmpty() || !$user) {
            return;
        }

        // Create sales orders
        $orders = [
            ['customer_id' => 1, 'status' => 'delivered', 'days_ago' => 15],
            ['customer_id' => 2, 'status' => 'shipped', 'days_ago' => 5],
            ['customer_id' => 3, 'status' => 'processing', 'days_ago' => 2],
            ['customer_id' => 1, 'status' => 'confirmed', 'days_ago' => 1],
            ['customer_id' => 4, 'status' => 'draft', 'days_ago' => 0],
            ['customer_id' => 5, 'status' => 'delivered', 'days_ago' => 30],
            ['customer_id' => 2, 'status' => 'delivered', 'days_ago' => 25],
            ['customer_id' => 3, 'status' => 'cancelled', 'days_ago' => 10],
            ['customer_id' => 4, 'status' => 'processing', 'days_ago' => 3],
            ['customer_id' => 5, 'status' => 'confirmed', 'days_ago' => 1],
        ];

        foreach ($orders as $index => $orderData) {
            $orderDate = now()->subDays($orderData['days_ago']);
            
            $order = SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'customer_id' => $orderData['customer_id'],
                'user_id' => $user->id,
                'order_date' => $orderDate,
                'expected_delivery_date' => $orderDate->copy()->addDays(7),
                'status' => $orderData['status'],
                'shipping_address' => Customer::find($orderData['customer_id'])->address,
                'notes' => $index % 3 === 0 ? 'Priority order' : null,
            ]);

            // Add 1-4 random items to each order
            $orderItems = $items->random(rand(1, min(4, $items->count())));
            $subtotal = 0;

            foreach ($orderItems as $item) {
                $quantity = rand(1, 5);
                $unitPrice = $item->selling_price;
                $discount = rand(0, 1) ? rand(5, 15) : 0;
                $total = ($unitPrice * $quantity) - $discount;
                $subtotal += $total;

                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total' => $total,
                ]);
            }

            $tax = $subtotal * 0.11; // 11% PPN
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ]);
        }
    }
}
