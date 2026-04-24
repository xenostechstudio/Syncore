<?php

namespace Database\Factories\HR;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'employment_type' => 'permanent',
            'status' => 'active',
            'basic_salary' => fake()->numberBetween(5000000, 20000000),
            'hire_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
        ];
    }

    public function terminated(): static
    {
        return $this->state(fn () => ['status' => 'terminated']);
    }

    public function resigned(): static
    {
        return $this->state(fn () => ['status' => 'resigned']);
    }
}
