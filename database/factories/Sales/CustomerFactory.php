<?php

namespace Database\Factories\Sales;

use App\Models\Sales\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['person', 'company']),
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => 'Indonesia',
            'notes' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }
}
