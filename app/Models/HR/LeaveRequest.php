<?php

namespace App\Models\HR;

use App\Enums\LeaveRequestState;
use App\Models\User;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model
{
    use LogsActivity, HasNotes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'decimal:1',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStateAttribute(): LeaveRequestState
    {
        return LeaveRequestState::tryFrom($this->status) ?? LeaveRequestState::DRAFT;
    }

    public function transitionTo(LeaveRequestState $state): bool
    {
        $this->status = $state->value;
        return $this->save();
    }

    public function submit(): bool
    {
        if (!$this->state->canSubmit()) {
            return false;
        }
        return $this->transitionTo(LeaveRequestState::PENDING);
    }

    public function approve(int $userId): bool
    {
        if (!$this->state->canApprove()) {
            return false;
        }

        $this->approved_by = $userId;
        $this->approved_at = now();
        $result = $this->transitionTo(LeaveRequestState::APPROVED);

        if ($result) {
            // Update leave balance
            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $this->employee_id,
                    'leave_type_id' => $this->leave_type_id,
                    'year' => $this->start_date->year,
                ],
                [
                    'allocated' => $this->leaveType->days_per_year,
                    'used' => 0,
                    'carried_over' => 0,
                ]
            );
            $balance->increment('used', $this->days);
        }

        return $result;
    }

    public function reject(int $userId, string $reason = ''): bool
    {
        if (!$this->state->canReject()) {
            return false;
        }

        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        return $this->transitionTo(LeaveRequestState::REJECTED);
    }

    public function cancel(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(LeaveRequestState::CANCELLED);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_id', 'leave_type_id', 'start_date', 'end_date', 'days', 'reason', 'status', 'approved_by', 'rejection_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Leave request created',
                'updated' => 'Leave request updated',
                'deleted' => 'Leave request deleted',
                default => "Leave request {$eventName}",
            });
    }
}
