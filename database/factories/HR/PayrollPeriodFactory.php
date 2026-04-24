<?php

namespace Database\Factories\HR;

use App\Models\HR\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollPeriodFactory extends Factory
{
    protected $model = PayrollPeriod::class;

    public function definition(): array
    {
        return [
            'name' => 'Payroll ' . fake()->monthName() . ' ' . fake()->year(),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'draft',
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'employee_count' => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved', 'approved_at' => now()]);
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => 'processing']);
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid']);
    }
}
