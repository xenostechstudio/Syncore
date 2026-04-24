<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Warehouse',
            'location' => fake()->city(),
            'contact_info' => fake()->phoneNumber(),
        ];
    }
}
