<?php

namespace Database\Factories\Purchase;

use App\Enums\PurchaseOrderState;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseRfqFactory extends Factory
{
    protected $model = PurchaseRfq::class;

    public function definition(): array
    {
        $supplier = Supplier::factory()->create();

        return [
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'order_date' => now()->toDateString(),
            'expected_arrival' => now()->addDays(7)->toDateString(),
            'status' => PurchaseOrderState::PURCHASE_ORDER->value,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'notes' => null,
        ];
    }

    public function rfq(): static
    {
        return $this->state(fn () => ['status' => PurchaseOrderState::RFQ->value]);
    }

    public function purchaseOrder(): static
    {
        return $this->state(fn () => ['status' => PurchaseOrderState::PURCHASE_ORDER->value]);
    }

    public function received(): static
    {
        return $this->state(fn () => ['status' => PurchaseOrderState::RECEIVED->value]);
    }
}
