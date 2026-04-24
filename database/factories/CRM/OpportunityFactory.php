<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true) . ' Deal',
            'customer_id' => null,
            'pipeline_id' => Pipeline::factory(),
            'expected_revenue' => fake()->numberBetween(1000000, 100000000),
            'probability' => fake()->numberBetween(0, 100),
            'expected_close_date' => now()->addMonth()->toDateString(),
            'description' => fake()->optional()->sentence(),
            'status' => 'open',
        ];
    }

    public function won(): static
    {
        return $this->state(fn () => ['status' => 'won', 'won_at' => now()]);
    }

    public function lost(): static
    {
        return $this->state(fn () => ['status' => 'lost', 'lost_at' => now()]);
    }
}
