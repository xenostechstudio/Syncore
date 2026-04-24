<?php

namespace Database\Factories\Purchase;

use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorBillFactory extends Factory
{
    protected $model = VendorBill::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(100000, 10000000);

        return [
            'bill_number' => 'BILL-' . fake()->unique()->numerify('######'),
            'supplier_id' => Supplier::factory(),
            'bill_date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => round($total / 1.11),
            'tax' => round($total - ($total / 1.11)),
            'total' => $total,
            'paid_amount' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function paid(): static
    {
        return $this->state(fn (array $a) => [
            'status' => 'paid',
            'paid_amount' => $a['total'],
            'paid_date' => now()->toDateString(),
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn (array $a) => [
            'status' => 'partial',
            'paid_amount' => round($a['total'] * 0.5),
        ]);
    }
}
