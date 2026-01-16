<?php

namespace Database\Factories\Invoicing;

use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(100000, 10000000);
        $status = fake()->randomElement(['draft', 'sent', 'partial', 'paid', 'overdue']);
        $paidAmount = match ($status) {
            'paid' => $total,
            'partial' => round($total * fake()->randomFloat(2, 0.1, 0.9)),
            default => 0,
        };

        return [
            'customer_id' => Customer::factory(),
            'sales_order_id' => null,
            'invoice_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => $status,
            'subtotal' => round($total / 1.11),
            'tax' => round($total - ($total / 1.11)),
            'discount' => 0,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'paid_date' => $status === 'paid' ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'paid_amount' => 0,
            'paid_date' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'paid_amount' => 0,
            'paid_date' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['total'],
            'paid_date' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'paid_amount' => 0,
            'paid_date' => null,
        ]);
    }

    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'partial',
                'paid_amount' => round($attributes['total'] * 0.5),
                'paid_date' => null,
            ];
        });
    }
}
