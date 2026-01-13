<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $costPrice = fake()->numberBetween(10000, 500000);
        
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'barcode' => fake()->optional()->ean13(),
            'product_type' => fake()->randomElement(['storable', 'consumable', 'service']),
            'internal_reference' => fake()->optional()->bothify('REF-####'),
            'description' => fake()->optional()->paragraph(),
            'quantity' => fake()->numberBetween(0, 500),
            'cost_price' => $costPrice,
            'selling_price' => round($costPrice * fake()->randomFloat(2, 1.2, 2.0)),
            'status' => 'active',
            'warehouse_id' => null,
            'category_id' => null,
            'responsible_id' => null,
            'weight' => fake()->optional()->randomFloat(3, 0.1, 100),
            'volume' => fake()->optional()->randomFloat(3, 0.01, 10),
            'customer_lead_time' => fake()->numberBetween(1, 30),
            'is_favorite' => fake()->boolean(10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => ['quantity' => fake()->numberBetween(1, 10)]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['quantity' => 0]);
    }

    public function withCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::factory(),
        ]);
    }
}
