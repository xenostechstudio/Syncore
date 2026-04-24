<?php

namespace Database\Factories\HR;

use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => now()->toDateString(),
            'status' => 'present',
            'is_manual' => false,
        ];
    }

    public function manual(): static
    {
        return $this->state(fn () => ['is_manual' => true]);
    }

    public function absent(): static
    {
        return $this->state(fn () => ['status' => 'absent']);
    }
}
