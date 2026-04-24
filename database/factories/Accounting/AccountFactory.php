<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('####'),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
            'parent_id' => null,
            'description' => fake()->optional()->sentence(),
            'balance' => 0,
            'is_active' => true,
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn () => ['is_system' => true]);
    }

    public function asset(): static
    {
        return $this->state(fn () => ['type' => 'asset']);
    }
}
