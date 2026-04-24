<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'break_duration',
        'work_days',
        'is_flexible',
        'grace_period_minutes',
        'half_day_threshold_minutes',
        'is_active',
        'description',
    ];

    protected $casts = [
        'work_days' => 'array',
        'is_flexible' => 'boolean',
        'is_active' => 'boolean',
        'grace_period_minutes' => 'integer',
        'half_day_threshold_minutes' => 'integer',
        'break_duration' => 'integer',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function employeeSchedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function isWorkDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->work_days ?? []);
    }

    public function getTotalWorkMinutes(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        return $end->diffInMinutes($start) - $this->break_duration;
    }
}
