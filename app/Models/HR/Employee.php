<?php

namespace App\Models\HR;

use App\Enums\EmployeeStatus;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use LogsActivity, HasNotes, Searchable, HasAttachments;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['name', 'email', 'phone', 'mobile', 'id_number'];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'mobile',
        'birth_date',
        'gender',
        'marital_status',
        'nationality',
        'id_number',
        'address',
        'city',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'department_id',
        'position_id',
        'manager_id',
        'hire_date',
        'contract_end_date',
        'employment_type',
        'status',
        'basic_salary',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'tax_id',
        'photo',
        'notes',
        'hr_responsible_id',
        'pin_code',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    public function getEmployeeStatusAttribute(): EmployeeStatus
    {
        return EmployeeStatus::tryFrom($this->status) ?? EmployeeStatus::ACTIVE;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function employeeSalaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials ?: 'U';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->employeeStatus->color();
    }

    public function hrResponsible(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hr_responsible_id');
    }
}
