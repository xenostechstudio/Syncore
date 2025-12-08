<?php

namespace Database\Seeders;

use App\Models\Inventory\InventoryItem;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
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
                'order_number' => 'SO' . $orderDate->format('Ymd') . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
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
