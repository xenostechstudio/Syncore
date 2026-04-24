<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransferFactory extends Factory
{
    protected $model = InventoryTransfer::class;

    public function definition(): array
    {
        return [
            'transfer_number' => 'TRF-' . fake()->unique()->numerify('######'),
            'source_warehouse_id' => Warehouse::factory(),
            'destination_warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'transfer_date' => now()->toDateString(),
            'status' => 'draft',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
