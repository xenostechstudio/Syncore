<?php

namespace Database\Factories\HR;

use App\Models\HR\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalaryComponentFactory extends Factory
{
    protected $model = SalaryComponent::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('SC????')),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(['earning', 'deduction']),
            'calculation_type' => 'fixed',
            'default_amount' => fake()->numberBetween(100000, 5000000),
            'percentage' => null,
            'percentage_of' => null,
            'is_taxable' => true,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function earning(): static
    {
        return $this->state(fn () => ['type' => 'earning']);
    }

    public function deduction(): static
    {
        return $this->state(fn () => ['type' => 'deduction']);
    }
}
