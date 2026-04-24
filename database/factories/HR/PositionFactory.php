<?php

namespace Database\Factories\HR;

use App\Models\HR\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'department_id' => null,
            'description' => fake()->optional()->sentence(),
            'requirements' => null,
            'min_salary' => fake()->numberBetween(3000000, 8000000),
            'max_salary' => fake()->numberBetween(8000001, 25000000),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
