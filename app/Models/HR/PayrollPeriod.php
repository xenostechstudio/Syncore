<?php

namespace App\Models\HR;

use App\Enums\PayrollState;
use App\Models\User;
use App\Traits\HasCreatedBy;
use App\Traits\HasNotes;
use App\Traits\HasStateMachine;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use LogsActivity, HasNotes, HasStateMachine, HasCreatedBy;

    protected string $stateEnum = PayrollState::class;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name', 'start_date', 'end_date', 'payment_date', 'status',
        'total_gross', 'total_deductions', 'total_net', 'employee_count',
        'created_by', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recalculateTotals(): void
    {
        $this->total_gross = $this->items()->sum('total_earnings');
        $this->total_deductions = $this->items()->sum('total_deductions');
        $this->total_net = $this->items()->sum('net_salary');
        $this->employee_count = $this->items()->count();
        $this->save();
    }

    public function approve(int $userId): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }
        $this->approved_by = $userId;
        $this->approved_at = now();
        return $this->transitionTo(PayrollState::APPROVED);
    }

    public function startProcessing(): bool
    {
        if (!$this->canStartProcessing()) {
            return false;
        }
        return $this->transitionTo(PayrollState::PROCESSING);
    }

    public function markAsPaid(): bool
    {
        if (!$this->canBeMarkedAsPaid()) {
            return false;
        }
        return $this->transitionTo(PayrollState::PAID);
    }

    public function cancelPayroll(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }
        return $this->transitionTo(PayrollState::CANCELLED);
    }

    public function resetToDraft(): bool
    {
        if (!$this->canBeResetToDraft()) {
            return false;
        }
        $this->approved_by = null;
        $this->approved_at = null;
        return $this->transitionTo(PayrollState::DRAFT);
    }

    public function isLocked(): bool
    {
        return $this->state->isLocked();
    }

    public function canBeEdited(): bool
    {
        return $this->state->canEdit();
    }

    public function canBeApproved(): bool
    {
        return $this->state->canApprove() && $this->items()->count() > 0;
    }

    public function canStartProcessing(): bool
    {
        return $this->state->canStartProcessing();
    }

    public function canBeMarkedAsPaid(): bool
    {
        return $this->state->canMarkPaid();
    }

    public function canBeCancelled(): bool
    {
        return $this->state->canCancel();
    }

    public function canBeResetToDraft(): bool
    {
        return $this->state->canResetToDraft();
    }

    public function getStatusColorAttribute(): string
    {
        return $this->state->color();
    }
}
