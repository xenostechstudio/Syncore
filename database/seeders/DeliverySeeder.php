<?php

namespace Database\Seeders;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $warehouse = Warehouse::first();
        
        if (!$user || !$warehouse) {
            return;
        }

        // Create delivery orders for shipped/delivered sales orders
        $salesOrders = SalesOrder::whereIn('status', ['shipped', 'delivered', 'processing'])
            ->with('items', 'customer')
            ->get();

        $couriers = ['JNE', 'J&T Express', 'SiCepat', 'AnterAja', 'Ninja Express'];

        foreach ($salesOrders as $index => $salesOrder) {
            $deliveryDate = $salesOrder->order_date->copy()->addDays(rand(1, 3));
            
            $status = match($salesOrder->status) {
                'delivered' => 'delivered',
                'shipped' => 'in_transit',
                'processing' => rand(0, 1) ? 'picked' : 'pending',
                default => 'pending',
            };

            // Generate delivery number explicitly for seeding
            $deliveryNumber = 'DO' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            
            $delivery = DeliveryOrder::create([
                'delivery_number' => $deliveryNumber,
                'sales_order_id' => $salesOrder->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'delivery_date' => $deliveryDate,
                'actual_delivery_date' => $status === 'delivered' ? $deliveryDate->copy()->addDays(rand(1, 5)) : null,
                'status' => $status,
                'shipping_address' => $salesOrder->shipping_address ?? $salesOrder->customer->address,
                'recipient_name' => $salesOrder->customer->name,
                'recipient_phone' => $salesOrder->customer->phone,
                'tracking_number' => $status !== 'pending' ? strtoupper(substr(md5(rand()), 0, 12)) : null,
                'courier' => $couriers[array_rand($couriers)],
                'notes' => $index % 4 === 0 ? 'Handle with care' : null,
            ]);

            // Add delivery items
            foreach ($salesOrder->items as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'sales_order_item_id' => $item->id,
                    'quantity_to_deliver' => $item->quantity,
                    'quantity_delivered' => $status === 'delivered' ? $item->quantity : 0,
                ]);
            }
        }
    }
}
