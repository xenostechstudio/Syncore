<?php

namespace Database\Seeders;

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Tax;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ppn11 = Tax::firstOrCreate(
            ['code' => 'PPN11'],
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
        );

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

        $warehouses = Warehouse::orderBy('id')->get();
        $mainWarehouseId = $warehouses->first()?->id;
        $eastWarehouseId = $warehouses->skip(1)->first()?->id;
        $defaultWarehouseId = $mainWarehouseId;

        $seedStock = function (Product $product, int $warehouseId, int $qty): void {
            InventoryStock::query()->updateOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $qty,
                ]
            );
        };

        $product = Product::create([
            'name' => 'Apple MacBook Pro 16-inch (M3 Max, 14‑core CPU, 30‑core GPU, 36GB, 1TB) - Space Black',
            'sku' => 'APPLE-MBP16-M3MAX-36-1TB-SB',
            'description' => 'Apple MacBook Pro 16-inch with M3 Max, 36GB unified memory, 1TB SSD (Space Black).',
            'quantity' => 25,
            'cost_price' => 56000000.00,
            'selling_price' => 61499000.00,
            'status' => 'in_stock',
            'sales_tax_id' => $ppn11->id,
            'warehouse_id' => $defaultWarehouseId,
        ]);

        if ($defaultWarehouseId) {
            $seedStock($product, $defaultWarehouseId, 25);
        }

        $product = Product::create([
            'name' => 'Dell XPS 15 9530 (Core i7-13700H, 16GB, 512GB SSD, RTX 4050 6GB, 15.6" OLED 3.5K Touch)',
            'sku' => 'DELL-XPS15-9530-I7-16-512-RTX4050',
            'description' => 'Dell XPS 15 9530 with Intel Core i7-13700H, 16GB RAM, 512GB SSD, NVIDIA RTX 4050 6GB, 15.6" OLED 3.5K touchscreen.',
            'quantity' => 10,
            'cost_price' => 35999000.00,
            'selling_price' => 39999000.00,
            'status' => 'in_stock',
            'sales_tax_id' => $ppn11->id,
            'warehouse_id' => $eastWarehouseId ?: $defaultWarehouseId,
        ]);

        $warehouseId = $eastWarehouseId ?: $defaultWarehouseId;
        if ($warehouseId) {
            $seedStock($product, $warehouseId, 10);
        }

        $product = Product::create([
            'name' => 'LG UltraFine 5K 27" IPS (27MD5KA-B) - Thunderbolt 3',
            'sku' => 'LG-27MD5KA-B-5K',
            'description' => 'LG UltraFine 5K 27-inch IPS monitor (27MD5KA-B) with Thunderbolt 3 and USB-C connectivity.',
            'quantity' => 5,
            'cost_price' => 26999000.00,
            'selling_price' => 29999000.00,
            'status' => 'low_stock',
            'sales_tax_id' => $ppn11->id,
            'warehouse_id' => $defaultWarehouseId,
        ]);

        if ($defaultWarehouseId) {
            $seedStock($product, $defaultWarehouseId, 5);
        }

        $product = Product::create([
            'name' => 'Keychron K2 Pro QMK/VIA 75% Wireless Mechanical Keyboard (RGB, Aluminum Frame)',
            'sku' => 'KEYCHRON-K2PRO-RGB-ALU',
            'description' => 'Keychron K2 Pro QMK/VIA 75% compact wireless mechanical keyboard with RGB backlight and aluminum frame.',
            'quantity' => 0,
            'cost_price' => 1250000.00,
            'selling_price' => 1520000.00,
            'status' => 'out_of_stock',
            'sales_tax_id' => $ppn11->id,
            'warehouse_id' => $defaultWarehouseId,
        ]);

        if ($defaultWarehouseId) {
            $seedStock($product, $defaultWarehouseId, 0);
        }

        // Additional items for scroll testing
        $items = [
            ['name' => 'Apple iPhone 17 Pro Max 256GB - Cosmic Orange', 'sku' => 'APPLE-IP17PM-256-CO', 'description' => 'Apple iPhone 17 Pro Max 256GB (Cosmic Orange).', 'quantity' => 50, 'cost_price' => 23000000.00, 'selling_price' => 25749000.00, 'status' => 'in_stock'],
            ['name' => 'Apple iPad Pro 13-inch (M4) Wi‑Fi 512GB Standard Glass - Space Black', 'sku' => 'APPLE-IPADPRO13-M4-512-SB', 'description' => 'Apple iPad Pro 13-inch (M4) Wi‑Fi 512GB, Standard Glass (Space Black).', 'quantity' => 30, 'cost_price' => 28000000.00, 'selling_price' => 31499000.00, 'status' => 'in_stock'],
            ['name' => 'Apple AirPods Pro (2nd generation) with MagSafe Case (USB‑C)', 'sku' => 'APPLE-AIRPODS-PRO2-USBC', 'description' => 'Apple AirPods Pro (2nd generation) with MagSafe Charging Case (USB‑C).', 'quantity' => 100, 'cost_price' => 3150000.00, 'selling_price' => 3699000.00, 'status' => 'in_stock'],
            ['name' => 'Apple Magic Keyboard with Touch ID (US English)', 'sku' => 'APPLE-MK-TOUCHID-US', 'description' => 'Apple Magic Keyboard with Touch ID (US English).', 'quantity' => 8, 'cost_price' => 2550000.00, 'selling_price' => 2999000.00, 'status' => 'low_stock'],
            ['name' => 'Apple Magic Mouse - Black Multi-Touch Surface', 'sku' => 'APPLE-MAGICMOUSE-BLK', 'description' => 'Apple Magic Mouse (Black Multi-Touch Surface).', 'quantity' => 45, 'cost_price' => 1500000.00, 'selling_price' => 1799000.00, 'status' => 'in_stock'],
            ['name' => 'Apple Studio Display 27-inch 5K - Standard Glass - Tilt-adjustable stand', 'sku' => 'APPLE-STUDIODISPLAY-TILT', 'description' => 'Apple Studio Display 27-inch 5K, Standard Glass, Tilt-adjustable stand.', 'quantity' => 3, 'cost_price' => 25000000.00, 'selling_price' => 27999000.00, 'status' => 'low_stock'],
            ['name' => 'Apple Mac mini (M2, 8‑core CPU, 10‑core GPU, 256GB SSD)', 'sku' => 'APPLE-MACMINI-M2-256', 'description' => 'Apple Mac mini with M2 chip, 256GB SSD.', 'quantity' => 20, 'cost_price' => 9000000.00, 'selling_price' => 10499000.00, 'status' => 'in_stock'],
            ['name' => 'Apple Mac Studio (M2 Ultra, 24‑core CPU, 60‑core GPU, 64GB, 1TB)', 'sku' => 'APPLE-MACSTUDIO-M2U-64-1TB', 'description' => 'Apple Mac Studio with M2 Ultra, 64GB unified memory, 1TB SSD.', 'quantity' => 2, 'cost_price' => 64000000.00, 'selling_price' => 70999000.00, 'status' => 'low_stock'],
            ['name' => 'Apple Watch Ultra 2 GPS + Cellular 49mm Titanium Case with Ocean Band', 'sku' => 'APPLE-WATCHU2-49-OCEAN', 'description' => 'Apple Watch Ultra 2 GPS + Cellular 49mm Titanium Case with Ocean Band.', 'quantity' => 15, 'cost_price' => 14000000.00, 'selling_price' => 15999000.00, 'status' => 'in_stock'],
            ['name' => 'Apple HomePod mini - White', 'sku' => 'APPLE-HPMINI-WHT', 'description' => 'Apple HomePod mini (White).', 'quantity' => 60, 'cost_price' => 1800000.00, 'selling_price' => 2149000.00, 'status' => 'in_stock'],
            ['name' => 'Apple TV 4K (3rd generation) 128GB Wi‑Fi + Ethernet', 'sku' => 'APPLE-ATV4K-3RD-128-ETH', 'description' => 'Apple TV 4K (3rd generation) with 128GB storage (Wi‑Fi + Ethernet).', 'quantity' => 25, 'cost_price' => 2400000.00, 'selling_price' => 2857000.00, 'status' => 'in_stock'],
            ['name' => 'Apple AirTag (4 Pack)', 'sku' => 'APPLE-AIRTAG-4PK', 'description' => 'Apple AirTag (4 Pack).', 'quantity' => 80, 'cost_price' => 1250000.00, 'selling_price' => 1499000.00, 'status' => 'in_stock'],
            ['name' => 'Apple MagSafe Charger (1 m)', 'sku' => 'APPLE-MAGSAFE-1M', 'description' => 'Apple MagSafe Charger (1 m).', 'quantity' => 0, 'cost_price' => 800000.00, 'selling_price' => 969000.00, 'status' => 'out_of_stock'],
            ['name' => 'Apple 240W USB‑C Charge Cable (2 m)', 'sku' => 'APPLE-USBC-240W-2M', 'description' => 'Apple 240W USB‑C Charge Cable (2 m).', 'quantity' => 200, 'cost_price' => 450000.00, 'selling_price' => 549000.00, 'status' => 'in_stock'],
            ['name' => 'Apple Thunderbolt 4 (USB‑C) Pro Cable (1 m)', 'sku' => 'APPLE-TB4-PRO-1M', 'description' => 'Apple Thunderbolt 4 (USB‑C) Pro Cable (1 m).', 'quantity' => 12, 'cost_price' => 1250000.00, 'selling_price' => 1499000.00, 'status' => 'in_stock'],
            
            // Low-cost items (under 100,000 Rupiah)
            ['name' => 'Ballpoint Pen - Blue (Pack of 12)', 'sku' => 'OFC-PEN-BLUE-12', 'description' => 'Standard ballpoint pen, blue ink, pack of 12.', 'quantity' => 500, 'cost_price' => 15000.00, 'selling_price' => 24000.00, 'status' => 'in_stock'],
            ['name' => 'A4 Copy Paper 70gsm (500 sheets)', 'sku' => 'OFC-PAPER-A4-500', 'description' => 'A4 copy paper 70gsm, 500 sheets per ream.', 'quantity' => 200, 'cost_price' => 35000.00, 'selling_price' => 48000.00, 'status' => 'in_stock'],
            ['name' => 'Sticky Notes 3x3 inch - Yellow (100 sheets)', 'sku' => 'OFC-STICKY-3X3-YLW', 'description' => 'Yellow sticky notes 3x3 inch, 100 sheets.', 'quantity' => 300, 'cost_price' => 8000.00, 'selling_price' => 12000.00, 'status' => 'in_stock'],
            ['name' => 'Binder Clips 32mm (Box of 12)', 'sku' => 'OFC-BINDER-32MM-12', 'description' => 'Metal binder clips 32mm, box of 12.', 'quantity' => 150, 'cost_price' => 12000.00, 'selling_price' => 18000.00, 'status' => 'in_stock'],
            ['name' => 'Correction Tape 5mm x 8m', 'sku' => 'OFC-CORRTAPE-5X8', 'description' => 'Correction tape 5mm width, 8 meters length.', 'quantity' => 100, 'cost_price' => 6000.00, 'selling_price' => 9500.00, 'status' => 'in_stock'],
            ['name' => 'Highlighter Pen - Assorted Colors (Pack of 6)', 'sku' => 'OFC-HIGHLIGHT-6PK', 'description' => 'Highlighter pen set, 6 assorted colors.', 'quantity' => 80, 'cost_price' => 18000.00, 'selling_price' => 28000.00, 'status' => 'in_stock'],
            ['name' => 'Stapler Mini + Staples Set', 'sku' => 'OFC-STAPLER-MINI', 'description' => 'Mini stapler with 1000 staples included.', 'quantity' => 60, 'cost_price' => 15000.00, 'selling_price' => 25000.00, 'status' => 'in_stock'],
            ['name' => 'Paper Clips 28mm (Box of 100)', 'sku' => 'OFC-PAPERCLIP-100', 'description' => 'Metal paper clips 28mm, box of 100.', 'quantity' => 400, 'cost_price' => 5000.00, 'selling_price' => 8000.00, 'status' => 'in_stock'],
            ['name' => 'Rubber Band 100g Pack', 'sku' => 'OFC-RUBBER-100G', 'description' => 'Assorted rubber bands, 100g pack.', 'quantity' => 250, 'cost_price' => 8000.00, 'selling_price' => 12000.00, 'status' => 'in_stock'],
            ['name' => 'Pencil 2B (Pack of 12)', 'sku' => 'OFC-PENCIL-2B-12', 'description' => '2B pencils, pack of 12.', 'quantity' => 180, 'cost_price' => 12000.00, 'selling_price' => 18000.00, 'status' => 'in_stock'],
            ['name' => 'Eraser - White (Pack of 3)', 'sku' => 'OFC-ERASER-WHT-3', 'description' => 'White eraser, pack of 3.', 'quantity' => 200, 'cost_price' => 6000.00, 'selling_price' => 9000.00, 'status' => 'in_stock'],
            ['name' => 'Scissors 7 inch - Stainless Steel', 'sku' => 'OFC-SCISSORS-7IN', 'description' => 'Stainless steel scissors, 7 inch.', 'quantity' => 50, 'cost_price' => 18000.00, 'selling_price' => 28000.00, 'status' => 'in_stock'],
            ['name' => 'Glue Stick 21g', 'sku' => 'OFC-GLUE-21G', 'description' => 'Glue stick 21g, non-toxic.', 'quantity' => 120, 'cost_price' => 7000.00, 'selling_price' => 12000.00, 'status' => 'in_stock'],
            ['name' => 'Ruler 30cm - Plastic', 'sku' => 'OFC-RULER-30CM', 'description' => 'Plastic ruler 30cm with metric markings.', 'quantity' => 90, 'cost_price' => 4000.00, 'selling_price' => 7000.00, 'status' => 'in_stock'],
            ['name' => 'Envelope White A4 (Pack of 50)', 'sku' => 'OFC-ENV-A4-50', 'description' => 'White envelope A4 size, pack of 50.', 'quantity' => 70, 'cost_price' => 25000.00, 'selling_price' => 38000.00, 'status' => 'in_stock'],
            ['name' => 'Folder Clear L-Shape A4 (Pack of 10)', 'sku' => 'OFC-FOLDER-L-10', 'description' => 'Clear L-shape folder A4, pack of 10.', 'quantity' => 100, 'cost_price' => 15000.00, 'selling_price' => 22000.00, 'status' => 'in_stock'],
            ['name' => 'Notebook A5 Ruled 80 pages', 'sku' => 'OFC-NOTEBOOK-A5-80', 'description' => 'A5 ruled notebook, 80 pages.', 'quantity' => 150, 'cost_price' => 8000.00, 'selling_price' => 15000.00, 'status' => 'in_stock'],
            ['name' => 'Whiteboard Marker - Black', 'sku' => 'OFC-WBMARKER-BLK', 'description' => 'Whiteboard marker, black ink.', 'quantity' => 200, 'cost_price' => 6000.00, 'selling_price' => 10000.00, 'status' => 'in_stock'],
            ['name' => 'USB Flash Drive 8GB', 'sku' => 'TECH-USB-8GB', 'description' => 'USB 2.0 flash drive, 8GB capacity.', 'quantity' => 50, 'cost_price' => 25000.00, 'selling_price' => 35000.00, 'status' => 'in_stock'],
            ['name' => 'Mouse Pad Basic - Black', 'sku' => 'TECH-MOUSEPAD-BLK', 'description' => 'Basic mouse pad, black, 22x18cm.', 'quantity' => 80, 'cost_price' => 12000.00, 'selling_price' => 20000.00, 'status' => 'in_stock'],
        ];

        foreach ($items as $item) {
            $warehouseId = ($item['quantity'] ?? 0) % 2 === 0
                ? ($defaultWarehouseId ?: $warehouses->first()?->id)
                : ($eastWarehouseId ?: $defaultWarehouseId ?: $warehouses->first()?->id);

            $product = Product::create($item + [
                'sales_tax_id' => $ppn11->id,
                'warehouse_id' => $warehouseId,
            ]);

            if ($warehouseId) {
                $seedStock($product, $warehouseId, (int) ($item['quantity'] ?? 0));
            }
        }
    }
}
