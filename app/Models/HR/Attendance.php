<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'work_schedule_id',
        'check_in_time',
        'check_in_location',
        'check_in_photo',
        'check_in_device',
        'check_in_ip',
        'check_in_notes',
        'check_out_time',
        'check_out_location',
        'check_out_photo',
        'check_out_device',
        'check_out_ip',
        'check_out_notes',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'work_duration_minutes',
        'is_manual',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_manual' => 'boolean',
        'approved_at' => 'datetime',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'work_duration_minutes' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function checkIn(array $data): void
    {
        $this->update([
            'check_in_time' => now()->format('H:i:s'),
            'check_in_location' => $data['location'] ?? null,
            'check_in_photo' => $data['photo'] ?? null,
            'check_in_device' => $data['device'] ?? 'web',
            'check_in_ip' => request()->ip(),
            'check_in_notes' => $data['notes'] ?? null,
        ]);

        $this->calculateStatus();
    }

    public function checkOut(array $data): void
    {
        $this->update([
            'check_out_time' => now()->format('H:i:s'),
            'check_out_location' => $data['location'] ?? null,
            'check_out_photo' => $data['photo'] ?? null,
            'check_out_device' => $data['device'] ?? 'web',
            'check_out_ip' => request()->ip(),
            'check_out_notes' => $data['notes'] ?? null,
        ]);

        $this->calculateWorkDuration();
        $this->calculateStatus();
    }

    public function calculateStatus(): void
    {
        if (!$this->workSchedule || !$this->check_in_time) {
            return;
        }

        $scheduledStart = \Carbon\Carbon::parse($this->workSchedule->start_time);
        $actualStart = \Carbon\Carbon::parse($this->check_in_time);
        
        $lateMinutes = max(0, $actualStart->diffInMinutes($scheduledStart, false));
        $gracePeriod = $this->workSchedule->grace_period_minutes;
        $halfDayThreshold = $this->workSchedule->half_day_threshold_minutes;

        $this->late_minutes = $lateMinutes;

        if ($lateMinutes >= $halfDayThreshold) {
            $this->status = 'half_day';
        } elseif ($lateMinutes > $gracePeriod) {
            $this->status = 'late';
        } else {
            $this->status = 'present';
        }

        $this->save();
    }

    public function calculateWorkDuration(): void
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return;
        }

        $checkIn = \Carbon\Carbon::parse($this->check_in_time);
        $checkOut = \Carbon\Carbon::parse($this->check_out_time);
        
        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        $breakDuration = $this->workSchedule?->break_duration ?? 0;
        
        $this->work_duration_minutes = max(0, $totalMinutes - $breakDuration);

        if ($this->workSchedule) {
            $scheduledEnd = \Carbon\Carbon::parse($this->workSchedule->end_time);
            $this->early_leave_minutes = max(0, $scheduledEnd->diffInMinutes($checkOut, false));
            
            $expectedMinutes = $this->workSchedule->getTotalWorkMinutes();
            $this->overtime_minutes = max(0, $this->work_duration_minutes - $expectedMinutes);
        }

        $this->save();
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isPresent(): bool
    {
        return in_array($this->status, ['present', 'late']);
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }
}
