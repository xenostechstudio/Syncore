<?php

namespace Database\Seeders;

use App\Enums\SalesOrderState;
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

        // Walk the seeded confirmed sales orders and give ~60% a delivery
        // order with a varied status so the Delivery dashboard isn't empty.
        $salesOrders = SalesOrder::where('status', SalesOrderState::SALES_ORDER->value)
            ->with('items', 'customer')
            ->orderBy('id')
            ->get();

        $couriers = ['JNE', 'J&T Express', 'SiCepat', 'AnterAja', 'Ninja Express'];
        // Status distribution. Order matters — we cycle through this as
        // we walk the orders, skipping every ~3rd to leave some undelivered.
        $statusCycle = ['delivered', 'in_transit', 'delivered', 'picked', 'pending', 'delivered'];
        $cycleIdx = 0;

        foreach ($salesOrders as $index => $salesOrder) {
            if ($index % 3 === 2) {
                continue;
            }

            $status = $statusCycle[$cycleIdx % count($statusCycle)];
            $cycleIdx++;

            $deliveryDate = $salesOrder->order_date->copy()->addDays(rand(1, 3));

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

            // Add delivery items; the SO item's quantity_delivered
            // counter is recomputed by DeliveryOrderItemObserver (and the
            // delivered-status filter is enforced inside the service).
            foreach ($salesOrder->items as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $delivery->id,
                    'sales_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'quantity_delivered' => $status === 'delivered' ? $item->quantity : 0,
                ]);
            }
        }
    }
}
