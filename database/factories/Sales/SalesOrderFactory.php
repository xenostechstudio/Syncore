<?php

namespace Database\Factories\Sales;

use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'order_number' => 'SO-' . fake()->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'order_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => fake()->randomElement(['draft', 'confirmed', 'processing', 'delivered']),
            'subtotal' => $subtotal = fake()->numberBetween(100000, 10000000),
            'tax' => $tax = round($subtotal * 0.11),
            'discount' => 0,
            'total' => $subtotal + $tax,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'draft']);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'confirmed']);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'processing']);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'delivered']);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'cancelled']);
    }
}
