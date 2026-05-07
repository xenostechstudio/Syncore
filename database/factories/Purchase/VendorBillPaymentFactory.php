<?php

namespace Database\Factories\Purchase;

use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorBillPaymentFactory extends Factory
{
    protected $model = VendorBillPayment::class;

    public function definition(): array
    {
        return [
            'vendor_bill_id' => VendorBill::factory(),
            'payment_date'   => now(),
            'amount'         => $this->faker->randomFloat(2, 100, 1000),
            'payment_method' => 'Bank Transfer',
            'reference'      => 'TRX' . $this->faker->unique()->randomNumber(6),
        ];
    }
}
