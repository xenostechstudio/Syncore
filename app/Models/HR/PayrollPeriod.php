<?php

namespace App\Models\HR;

use App\Enums\PayrollState;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use LogsActivity, HasNotes;

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

    public function getStateAttribute(): PayrollState
    {
        return PayrollState::tryFrom($this->status) ?? PayrollState::DRAFT;
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
