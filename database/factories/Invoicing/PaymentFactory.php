<?php

namespace Database\Factories\Invoicing;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => 'PAY-' . fake()->unique()->numerify('######'),
            'invoice_id' => Invoice::factory(),
            'payment_date' => now()->toDateString(),
            'amount' => fake()->numberBetween(100000, 10000000),
            'payment_method' => fake()->randomElement(['bank_transfer', 'cash', 'credit_card']),
            'reference' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
            'status' => 'completed',
        ];
    }
}
