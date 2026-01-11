<?php

namespace App\Models\HR;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'code',
        'days_per_year',
        'is_paid',
        'requires_approval',
        'is_active',
        'description',
    ];

    protected $casts = [
        'days_per_year' => 'integer',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
