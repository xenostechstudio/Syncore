<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineFactory extends Factory
{
    protected $model = Pipeline::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sequence' => fake()->numberBetween(1, 6),
            'color' => fake()->randomElement(['zinc', 'blue', 'amber', 'violet', 'emerald', 'red']),
            'probability' => fake()->randomFloat(2, 0, 100),
            'is_won' => false,
            'is_lost' => false,
            'is_active' => true,
        ];
    }

    public function won(): static
    {
        return $this->state(fn () => ['is_won' => true, 'probability' => 100]);
    }

    public function lost(): static
    {
        return $this->state(fn () => ['is_lost' => true, 'probability' => 0]);
    }
}
