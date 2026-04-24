<?php

namespace Database\Factories\Sales;

use App\Models\Sales\Pricelist;
use Illuminate\Database\Eloquent\Factories\Factory;

class PricelistFactory extends Factory
{
    protected $model = Pricelist::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('PL????')),
            'currency' => 'IDR',
            'type' => 'percentage',
            'discount' => fake()->randomFloat(2, 0, 25),
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
