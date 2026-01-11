<?php

namespace App\Models\HR;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItem extends Model
{
    use HasNotes, LogsActivity;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'payroll_period_id', 'employee_id', 'basic_salary',
        'total_earnings', 'total_deductions', 'net_salary',
        'working_days', 'days_worked', 'leave_days', 'absent_days',
        'status', 'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollItemDetail::class);
    }

    public function recalculate(): void
    {
        $this->total_earnings = $this->details()->where('type', 'earning')->sum('amount');
        $this->total_deductions = $this->details()->where('type', 'deduction')->sum('amount');
        $this->net_salary = $this->basic_salary + $this->total_earnings - $this->total_deductions;
        $this->save();
    }
}
