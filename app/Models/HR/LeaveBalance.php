<?php

namespace App\Models\HR;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use LogsActivity;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated',
        'used',
        'carried_over',
    ];

    protected $casts = [
        'year' => 'integer',
        'allocated' => 'decimal:1',
        'used' => 'decimal:1',
        'carried_over' => 'decimal:1',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingAttribute(): float
    {
        return $this->allocated + $this->carried_over - $this->used;
    }
}
