<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function isEffectiveOn(\Carbon\Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($date->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    public static function getActiveScheduleForEmployee(int $employeeId, \Carbon\Carbon $date): ?self
    {
        return static::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            })
            ->with('workSchedule')
            ->first();
    }
}
