<?php

namespace Database\Seeders;

use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Jakarta, Indonesia',
            'contact_info' => '+62 21 555 0100',
        ]);

        Warehouse::create([
            'name' => 'East Branch',
            'location' => 'Surabaya, Indonesia',
            'contact_info' => '+62 31 555 0200',
        ]);

        Product::create([
            'name' => 'MacBook Pro 16"',
            'sku' => 'MBP-16-2024',
            'description' => 'Apple MacBook Pro 16" with M3 Max',
            'quantity' => 25,
            'cost_price' => 2500.00,
            'selling_price' => 3200.00,
            'status' => 'in_stock',
        ]);

        Product::create([
            'name' => 'Dell XPS 15',
            'sku' => 'DELL-XPS-15',
            'description' => 'Dell XPS 15 Laptop',
            'quantity' => 10,
            'cost_price' => 1800.00,
            'selling_price' => 2400.00,
            'status' => 'in_stock',
        ]);

        Product::create([
            'name' => 'Monitor LG UltraFine',
            'sku' => 'LG-UF-27',
            'description' => 'LG UltraFine 5K Display',
            'quantity' => 5,
            'cost_price' => 800.00,
            'selling_price' => 1299.00,
            'status' => 'low_stock',
        ]);

        Product::create([
            'name' => 'Keychron K2 Pro',
            'sku' => 'KC-K2P-RGB',
            'description' => 'Mechanical Keyboard Wireless',
            'quantity' => 0,
            'cost_price' => 80.00,
            'selling_price' => 120.00,
            'status' => 'out_of_stock',
        ]);

        // Additional items for scroll testing
        $items = [
            ['name' => 'iPhone 15 Pro Max', 'sku' => 'IPH-15PM-256', 'description' => 'Apple iPhone 15 Pro Max 256GB', 'quantity' => 50, 'cost_price' => 1100.00, 'selling_price' => 1399.00, 'status' => 'in_stock'],
            ['name' => 'iPad Pro 12.9"', 'sku' => 'IPAD-PRO-12', 'description' => 'Apple iPad Pro 12.9" M2', 'quantity' => 30, 'cost_price' => 900.00, 'selling_price' => 1199.00, 'status' => 'in_stock'],
            ['name' => 'AirPods Pro 2', 'sku' => 'APP-2-USB', 'description' => 'Apple AirPods Pro 2nd Gen', 'quantity' => 100, 'cost_price' => 180.00, 'selling_price' => 249.00, 'status' => 'in_stock'],
            ['name' => 'Magic Keyboard', 'sku' => 'MK-TOUCH-ID', 'description' => 'Apple Magic Keyboard with Touch ID', 'quantity' => 8, 'cost_price' => 150.00, 'selling_price' => 199.00, 'status' => 'low_stock'],
            ['name' => 'Magic Mouse', 'sku' => 'MM-3-BLK', 'description' => 'Apple Magic Mouse Black', 'quantity' => 45, 'cost_price' => 80.00, 'selling_price' => 99.00, 'status' => 'in_stock'],
            ['name' => 'Studio Display', 'sku' => 'ASD-27-5K', 'description' => 'Apple Studio Display 27" 5K', 'quantity' => 3, 'cost_price' => 1400.00, 'selling_price' => 1599.00, 'status' => 'low_stock'],
            ['name' => 'Mac Mini M2', 'sku' => 'MM-M2-512', 'description' => 'Apple Mac Mini M2 512GB', 'quantity' => 20, 'cost_price' => 550.00, 'selling_price' => 699.00, 'status' => 'in_stock'],
            ['name' => 'Mac Studio', 'sku' => 'MS-M2U-1TB', 'description' => 'Apple Mac Studio M2 Ultra', 'quantity' => 2, 'cost_price' => 3500.00, 'selling_price' => 3999.00, 'status' => 'low_stock'],
            ['name' => 'Apple Watch Ultra 2', 'sku' => 'AWU-2-49', 'description' => 'Apple Watch Ultra 2 49mm', 'quantity' => 15, 'cost_price' => 700.00, 'selling_price' => 799.00, 'status' => 'in_stock'],
            ['name' => 'HomePod Mini', 'sku' => 'HPM-2-WHT', 'description' => 'Apple HomePod Mini White', 'quantity' => 60, 'cost_price' => 80.00, 'selling_price' => 99.00, 'status' => 'in_stock'],
            ['name' => 'Apple TV 4K', 'sku' => 'ATV-4K-128', 'description' => 'Apple TV 4K 128GB', 'quantity' => 25, 'cost_price' => 130.00, 'selling_price' => 149.00, 'status' => 'in_stock'],
            ['name' => 'AirTag 4 Pack', 'sku' => 'AT-4PK', 'description' => 'Apple AirTag 4 Pack', 'quantity' => 80, 'cost_price' => 80.00, 'selling_price' => 99.00, 'status' => 'in_stock'],
            ['name' => 'MagSafe Charger', 'sku' => 'MSC-15W', 'description' => 'Apple MagSafe Charger 15W', 'quantity' => 0, 'cost_price' => 30.00, 'selling_price' => 39.00, 'status' => 'out_of_stock'],
            ['name' => 'USB-C Cable 2m', 'sku' => 'USBC-2M-WVN', 'description' => 'Apple USB-C Woven Cable 2m', 'quantity' => 200, 'cost_price' => 25.00, 'selling_price' => 35.00, 'status' => 'in_stock'],
            ['name' => 'Thunderbolt 4 Cable', 'sku' => 'TB4-1M-PRO', 'description' => 'Apple Thunderbolt 4 Pro Cable', 'quantity' => 12, 'cost_price' => 100.00, 'selling_price' => 129.00, 'status' => 'in_stock'],
        ];

        foreach ($items as $item) {
            Product::create($item);
        }
    }
}
