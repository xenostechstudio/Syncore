<?php

namespace Database\Factories\HR;

use App\Models\HR\Employee;
use App\Models\HR\PayrollItem;
use App\Models\HR\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollItemFactory extends Factory
{
    protected $model = PayrollItem::class;

    public function definition(): array
    {
        return [
            'payroll_period_id' => PayrollPeriod::factory(),
            'employee_id' => Employee::factory(),
            'basic_salary' => 5000000,
            'total_earnings' => 0,
            'total_deductions' => 0,
            'net_salary' => 5000000,
            'working_days' => 22,
            'days_worked' => 22,
            'leave_days' => 0,
            'absent_days' => 0,
            'status' => 'draft',
        ];
    }
}
