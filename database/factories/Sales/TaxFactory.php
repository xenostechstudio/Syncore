<?php

namespace Database\Factories\Sales;

use App\Models\Sales\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('TX????')),
            'rate' => fake()->randomFloat(2, 1, 25),
            'type' => 'percentage',
            'scope' => 'both',
            'is_active' => true,
            'include_in_price' => false,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
