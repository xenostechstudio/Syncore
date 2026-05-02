<?php

namespace Database\Factories\Purchase;

use App\Models\Inventory\Product;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseRfqItemFactory extends Factory
{
    protected $model = PurchaseRfqItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = fake()->numberBetween(1000, 100000);

        return [
            'purchase_rfq_id' => PurchaseRfq::factory(),
            'product_id' => Product::factory(),
            'description' => fake()->optional()->sentence(),
            'quantity' => $quantity,
            'quantity_received' => 0,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
