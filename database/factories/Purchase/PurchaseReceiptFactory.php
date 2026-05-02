<?php

namespace Database\Factories\Purchase;

use App\Enums\PurchaseReceiptState;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Purchase\PurchaseRfq;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReceiptFactory extends Factory
{
    protected $model = PurchaseReceipt::class;

    public function definition(): array
    {
        $rfq = PurchaseRfq::factory()->create();

        return [
            'purchase_rfq_id' => $rfq->id,
            'supplier_id' => $rfq->supplier_id,
            'warehouse_id' => Warehouse::factory(),
            'received_by' => null,
            'received_at' => null,
            'status' => PurchaseReceiptState::DRAFT->value,
            'notes' => null,
        ];
    }

    public function validated(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseReceiptState::VALIDATED->value,
            'received_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => PurchaseReceiptState::CANCELLED->value]);
    }
}
