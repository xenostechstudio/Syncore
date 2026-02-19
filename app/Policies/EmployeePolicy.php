<?php

namespace App\Policies;

use App\Models\HR\Employee;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view employees');
    }

    public function view(User $user, Employee $employee): bool
    {
        // Users can view their own employee record
        if ($employee->user_id === $user->id) {
            return true;
        }

        return $user->can('view employees');
    }

    public function create(User $user): bool
    {
        return $user->can('create employees');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('edit employees');
    }

    public function delete(User $user, Employee $employee): bool
    {
        if (!$user->can('delete employees')) {
            return false;
        }

        // Cannot delete if employee has payroll records
        if ($employee->payrollItems()->exists()) {
            return false;
        }

        return true;
    }

    public function viewSalary(User $user, Employee $employee): bool
    {
        // Users can view their own salary
        if ($employee->user_id === $user->id) {
            return true;
        }

        return $user->can('view employee salaries');
    }

    public function editSalary(User $user, Employee $employee): bool
    {
        return $user->can('edit employee salaries');
    }

    public function viewLeaveBalance(User $user, Employee $employee): bool
    {
        if ($employee->user_id === $user->id) {
            return true;
        }

        return $user->can('view leave balances');
    }
}
