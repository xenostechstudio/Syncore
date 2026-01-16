<?php

namespace Database\Seeders;

use App\Enums\SalesOrderState;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\PaymentTerm;
use App\Models\Sales\Pricelist;
use App\Models\Sales\PricelistItem;
use App\Models\Sales\Promotion;
use App\Models\Sales\PromotionReward;
use App\Models\Sales\PromotionRule;
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

        // Seed Pricelists
        $pricelists = [
            [
                'name' => 'Retail Price',
                'code' => 'RETAIL',
                'currency' => 'IDR',
                'type' => 'fixed',
                'discount' => 0,
                'is_active' => true,
                'description' => 'Harga retail standar untuk pelanggan umum.',
            ],
            [
                'name' => 'Wholesale Price',
                'code' => 'WHOLESALE',
                'currency' => 'IDR',
                'type' => 'percentage',
                'discount' => 15,
                'is_active' => true,
                'description' => 'Harga grosir dengan diskon 15% dari harga retail.',
            ],
            [
                'name' => 'Distributor Price',
                'code' => 'DISTRIBUTOR',
                'currency' => 'IDR',
                'type' => 'percentage',
                'discount' => 25,
                'is_active' => true,
                'description' => 'Harga khusus distributor dengan diskon 25%.',
            ],
            [
                'name' => 'VIP Customer',
                'code' => 'VIP',
                'currency' => 'IDR',
                'type' => 'percentage',
                'discount' => 10,
                'is_active' => true,
                'description' => 'Harga khusus pelanggan VIP dengan diskon 10%.',
            ],
            [
                'name' => 'Promo Akhir Tahun',
                'code' => 'PROMO_EOY',
                'currency' => 'IDR',
                'type' => 'percentage',
                'discount' => 20,
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'is_active' => true,
                'description' => 'Promo akhir tahun dengan diskon 20%.',
            ],
        ];

        $createdPricelists = [];
        foreach ($pricelists as $pricelist) {
            $createdPricelists[] = Pricelist::firstOrCreate(
                ['code' => $pricelist['code']],
                $pricelist,
            );
        }

        // Assign all products to each pricelist with calculated prices
        $products = Product::all();
        
        foreach ($createdPricelists as $pricelist) {
            foreach ($products as $product) {
                $basePrice = $product->selling_price ?? 0;
                
                // Calculate price based on pricelist type
                if ($pricelist->type === 'percentage' && $pricelist->discount > 0) {
                    $price = $basePrice * (1 - ($pricelist->discount / 100));
                } else {
                    $price = $basePrice;
                }

                // Add some variation for fixed pricelists
                if ($pricelist->code === 'RETAIL') {
                    $price = $basePrice; // Keep original price
                }

                PricelistItem::firstOrCreate(
                    [
                        'pricelist_id' => $pricelist->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'price' => round($price, 2),
                        'min_quantity' => $pricelist->code === 'WHOLESALE' ? 10 : ($pricelist->code === 'DISTRIBUTOR' ? 50 : 1),
                        'start_date' => $pricelist->start_date,
                        'end_date' => $pricelist->end_date,
                    ],
                );
            }
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

        // Get pricelists for customer assignment
        $retailPricelist = Pricelist::where('code', 'RETAIL')->first();
        $wholesalePricelist = Pricelist::where('code', 'WHOLESALE')->first();
        $vipPricelist = Pricelist::where('code', 'VIP')->first();
        $distributorPricelist = Pricelist::where('code', 'DISTRIBUTOR')->first();

        // Create customers with assigned pricelists
        $customers = [
            ['name' => 'PT Maju Bersama', 'email' => 'contact@majubersama.co.id', 'phone' => '+62 21 5551234', 'address' => 'Jl. Sudirman No. 123', 'city' => 'Jakarta', 'status' => 'active', 'pricelist_id' => $vipPricelist?->id],
            ['name' => 'CV Teknologi Nusantara', 'email' => 'info@teknusa.com', 'phone' => '+62 21 5555678', 'address' => 'Jl. Gatot Subroto No. 45', 'city' => 'Jakarta', 'status' => 'active', 'pricelist_id' => $wholesalePricelist?->id],
            ['name' => 'Toko Elektronik Jaya', 'email' => 'sales@ejaya.com', 'phone' => '+62 22 4441234', 'address' => 'Jl. Asia Afrika No. 78', 'city' => 'Bandung', 'status' => 'active', 'pricelist_id' => $retailPricelist?->id],
            ['name' => 'PT Digital Prima', 'email' => 'order@digitalprima.id', 'phone' => '+62 31 8881234', 'address' => 'Jl. Pemuda No. 56', 'city' => 'Surabaya', 'status' => 'active', 'pricelist_id' => $distributorPricelist?->id],
            ['name' => 'CV Solusi Kreatif', 'email' => 'hello@solusikreatif.com', 'phone' => '+62 24 7771234', 'address' => 'Jl. Pandanaran No. 89', 'city' => 'Semarang', 'status' => 'active', 'pricelist_id' => $wholesalePricelist?->id],
            ['name' => 'Toko Komputer Mega', 'email' => 'mega@komputer.com', 'phone' => '+62 61 4561234', 'address' => 'Jl. Imam Bonjol No. 12', 'city' => 'Medan', 'status' => 'inactive', 'pricelist_id' => null],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Get items and user for orders
        $mainWarehouseId = Warehouse::query()->orderBy('id')->value('id');

        $items = $mainWarehouseId
            ? Product::query()->where('warehouse_id', $mainWarehouseId)->get()
            : Product::all();

        if ($items->isEmpty()) {
            $items = Product::all();
        }
        $user = User::first();
        
        if ($items->isEmpty() || !$user) {
            return;
        }

        // Create sales orders
        $orders = [
            ['customer_id' => 1, 'days_ago' => 15],
            ['customer_id' => 2, 'days_ago' => 5],
            ['customer_id' => 3, 'days_ago' => 2],
            ['customer_id' => 1, 'days_ago' => 1],
            ['customer_id' => 4, 'days_ago' => 0],
            ['customer_id' => 5, 'days_ago' => 30],
            ['customer_id' => 2, 'days_ago' => 25],
            ['customer_id' => 3, 'days_ago' => 10],
            ['customer_id' => 4, 'days_ago' => 3],
            ['customer_id' => 5, 'days_ago' => 1],
        ];

        foreach ($orders as $index => $orderData) {
            $orderDate = now()->subDays($orderData['days_ago']);
            $customer = Customer::find($orderData['customer_id']);
            
            // Generate order number explicitly for seeding
            $orderNumber = 'SO' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            
            $order = SalesOrder::create([
                'order_number' => $orderNumber,
                'customer_id' => $orderData['customer_id'],
                'user_id' => $user->id,
                'pricelist_id' => $customer?->pricelist_id,
                'order_date' => $orderDate,
                'expected_delivery_date' => $orderDate->copy()->addDays(7),
                'status' => SalesOrderState::SALES_ORDER->value,
                'shipping_address' => $customer?->address,
                'notes' => $index % 3 === 0 ? 'Priority order' : null,
            ]);

            // Add 1-4 random items to each order
            $orderItems = $items->random(rand(1, min(4, $items->count())));
            $subtotal = 0;

            foreach ($orderItems as $item) {
                $quantity = rand(1, 5);
                
                // Get price from pricelist if available
                $unitPrice = $item->selling_price;
                if ($customer?->pricelist_id) {
                    $pricelistItem = PricelistItem::where('pricelist_id', $customer->pricelist_id)
                        ->where('product_id', $item->id)
                        ->first();
                    if ($pricelistItem) {
                        $unitPrice = $pricelistItem->price;
                    }
                }
                
                $discount = rand(0, 1) ? rand(5, 15) : 0;
                $total = ($unitPrice * $quantity) - $discount;
                $subtotal += $total;

                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $item->id,
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

        // Seed Promotions
        $this->seedPromotions();
    }

    /**
     * Seed sample promotions
     */
    protected function seedPromotions(): void
    {
        $promotions = [
            // Buy 2 Get 1 Free
            [
                'name' => 'Buy 2 Get 1 Free',
                'code' => 'BOGO',
                'type' => 'buy_x_get_y',
                'priority' => 5,
                'is_combinable' => false,
                'requires_coupon' => false,
                'is_active' => true,
                'description' => 'Beli 2 produk, gratis 1 produk dengan harga terendah.',
                'reward' => [
                    'reward_type' => 'buy_x_get_y',
                    'buy_quantity' => 2,
                    'get_quantity' => 1,
                    'discount_value' => 100, // 100% off = free
                    'apply_to' => 'cheapest',
                ],
            ],
            // 10% Off Orders Over 1 Million
            [
                'name' => 'Diskon 10% Min. Order 1 Juta',
                'code' => null,
                'type' => 'cart_discount',
                'priority' => 10,
                'is_combinable' => false,
                'requires_coupon' => false,
                'min_order_amount' => 1000000,
                'is_active' => true,
                'description' => 'Diskon 10% untuk pembelian minimal Rp 1.000.000.',
                'reward' => [
                    'reward_type' => 'discount_percent',
                    'discount_value' => 10,
                    'max_discount' => 500000, // Max 500k discount
                    'apply_to' => 'order',
                ],
            ],
            // Coupon: SAVE20 - 20% Off
            [
                'name' => 'Kupon Diskon 20%',
                'code' => 'SAVE20',
                'type' => 'coupon',
                'priority' => 1,
                'is_combinable' => false,
                'requires_coupon' => true,
                'usage_limit' => 100,
                'usage_per_customer' => 1,
                'is_active' => true,
                'description' => 'Gunakan kode SAVE20 untuk diskon 20%.',
                'reward' => [
                    'reward_type' => 'discount_percent',
                    'discount_value' => 20,
                    'max_discount' => 1000000, // Max 1M discount
                    'apply_to' => 'order',
                ],
            ],
            // Coupon: FLAT50K - Rp 50.000 Off
            [
                'name' => 'Potongan Rp 50.000',
                'code' => 'FLAT50K',
                'type' => 'coupon',
                'priority' => 2,
                'is_combinable' => false,
                'requires_coupon' => true,
                'min_order_amount' => 200000,
                'usage_limit' => 50,
                'usage_per_customer' => 2,
                'is_active' => true,
                'description' => 'Potongan langsung Rp 50.000 untuk min. order Rp 200.000.',
                'reward' => [
                    'reward_type' => 'discount_fixed',
                    'discount_value' => 50000,
                    'apply_to' => 'order',
                ],
            ],
            // Quantity Break: 15% off for 10+ items
            [
                'name' => 'Diskon Grosir 15%',
                'code' => null,
                'type' => 'quantity_break',
                'priority' => 8,
                'is_combinable' => false,
                'requires_coupon' => false,
                'min_quantity' => 10,
                'is_active' => true,
                'description' => 'Diskon 15% untuk pembelian 10 item atau lebih.',
                'reward' => [
                    'reward_type' => 'discount_percent',
                    'discount_value' => 15,
                    'apply_to' => 'order',
                ],
            ],
            // New Customer Welcome
            [
                'name' => 'Welcome New Customer',
                'code' => 'WELCOME',
                'type' => 'coupon',
                'priority' => 3,
                'is_combinable' => false,
                'requires_coupon' => true,
                'usage_per_customer' => 1,
                'is_active' => true,
                'description' => 'Diskon 15% untuk pelanggan baru.',
                'reward' => [
                    'reward_type' => 'discount_percent',
                    'discount_value' => 15,
                    'max_discount' => 300000,
                    'apply_to' => 'order',
                ],
            ],
        ];

        foreach ($promotions as $promoData) {
            $rewardData = $promoData['reward'] ?? null;
            unset($promoData['reward']);

            $promotion = Promotion::firstOrCreate(
                ['name' => $promoData['name']],
                $promoData,
            );

            // Create reward if provided
            if ($rewardData && $promotion->wasRecentlyCreated) {
                $promotion->rewards()->create($rewardData);
            }
        }
    }
}
