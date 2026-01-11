<?php

namespace App\Models\HR;

use App\Enums\LeaveRequestState;
use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestRejected;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

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

            // Dispatch event for notifications
            event(new LeaveRequestApproved($this));
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
        $result = $this->transitionTo(LeaveRequestState::REJECTED);

        if ($result) {
            // Dispatch event for notifications
            event(new LeaveRequestRejected($this, $reason));
        }

        return $result;
    }

    public function cancel(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(LeaveRequestState::CANCELLED);
    }
}
