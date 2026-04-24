<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryAdjustmentFactory extends Factory
{
    protected $model = InventoryAdjustment::class;

    public function definition(): array
    {
        return [
            'adjustment_number' => 'ADJ-' . fake()->unique()->numerify('######'),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'adjustment_date' => now()->toDateString(),
            'adjustment_type' => fake()->randomElement(['increase', 'decrease', 'count']),
            'status' => 'draft',
            'reason' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
