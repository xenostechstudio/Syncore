<?php

namespace Database\Factories\HR;

use App\Models\HR\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleFactory extends Factory
{
    protected $model = WorkSchedule::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('WS????')),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_duration' => 60,
            'work_days' => [1, 2, 3, 4, 5],
            'is_flexible' => false,
            'grace_period_minutes' => 15,
            'half_day_threshold_minutes' => 240,
            'is_active' => true,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
