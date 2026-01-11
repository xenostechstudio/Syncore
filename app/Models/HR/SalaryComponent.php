<?php

namespace App\Models\HR;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'code', 'name', 'type', 'calculation_type', 'default_amount',
        'percentage', 'percentage_of', 'is_taxable', 'is_active', 'sort_order', 'description',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employeeComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public static function generateCode(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $number = $last ? (int) substr($last->code, 3) + 1 : 1;
        return 'SC-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
