<?php

namespace Database\Factories\Purchase;

use App\Models\Inventory\Product;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Purchase\PurchaseReceiptItem;
use App\Models\Purchase\PurchaseRfqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReceiptItemFactory extends Factory
{
    protected $model = PurchaseReceiptItem::class;

    public function definition(): array
    {
        return [
            'purchase_receipt_id' => PurchaseReceipt::factory(),
            'purchase_rfq_item_id' => PurchaseRfqItem::factory(),
            'product_id' => Product::factory(),
            'quantity_received' => fake()->numberBetween(1, 10),
        ];
    }
}
