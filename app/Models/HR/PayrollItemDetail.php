<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItemDetail extends Model
{
    protected $fillable = [
        'payroll_item_id', 'salary_component_id', 'component_name', 'type', 'source', 'amount', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    public function isAdjustment(): bool
    {
        return $this->source === 'adjustment';
    }

    public function isFromComponent(): bool
    {
        return $this->source === 'component';
    }
}
