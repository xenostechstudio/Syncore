<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            [
                'name' => 'Regular Shift',
                'code' => 'REG',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'break_duration' => 60,
                'work_days' => json_encode([1, 2, 3, 4, 5]), // Monday to Friday
                'is_flexible' => false,
                'grace_period_minutes' => 15,
                'half_day_threshold_minutes' => 240,
                'is_active' => true,
                'description' => 'Standard 9 AM to 5 PM shift, Monday to Friday',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Morning Shift',
                'code' => 'MORNING',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'break_duration' => 60,
                'work_days' => json_encode([1, 2, 3, 4, 5]),
                'is_flexible' => false,
                'grace_period_minutes' => 15,
                'half_day_threshold_minutes' => 240,
                'is_active' => true,
                'description' => '7 AM to 3 PM shift, Monday to Friday',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NIGHT',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'break_duration' => 60,
                'work_days' => json_encode([1, 2, 3, 4, 5]),
                'is_flexible' => false,
                'grace_period_minutes' => 15,
                'half_day_threshold_minutes' => 240,
                'is_active' => true,
                'description' => '10 PM to 6 AM shift, Monday to Friday',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Flexible Schedule',
                'code' => 'FLEX',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_duration' => 60,
                'work_days' => json_encode([1, 2, 3, 4, 5]),
                'is_flexible' => true,
                'grace_period_minutes' => 60,
                'half_day_threshold_minutes' => 240,
                'is_active' => true,
                'description' => 'Flexible working hours, 8 hours per day',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('work_schedules')->insert($schedules);
    }
}
