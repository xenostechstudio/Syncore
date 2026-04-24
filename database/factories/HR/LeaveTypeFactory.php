<?php

namespace Database\Factories\HR;

use App\Models\HR\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('LV????')),
            'days_per_year' => fake()->numberBetween(1, 30),
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
