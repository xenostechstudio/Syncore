<?php

namespace Database\Factories\Sales;

use App\Models\Sales\SalesTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesTeamFactory extends Factory
{
    protected $model = SalesTeam::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true) . ' Team',
            'description' => fake()->optional()->sentence(),
            'leader_id' => null,
            'target_amount' => fake()->numberBetween(10000000, 500000000),
            'is_active' => true,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
