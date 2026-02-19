<?php

namespace App\Policies;

use App\Models\HR\PayrollPeriod;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPeriodPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view payroll');
    }

    public function view(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->checkView($user, 'view payroll');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create payroll');
    }

    public function update(User $user, PayrollPeriod $payrollPeriod): bool
    {
        if (!$this->hasPermission($user, 'edit payroll')) {
            return false;
        }

        return $payrollPeriod->canBeEdited();
    }

    public function delete(User $user, PayrollPeriod $payrollPeriod): bool
    {
        if (!$this->hasPermission($user, 'delete payroll')) {
            return false;
        }

        return $payrollPeriod->state->canEdit();
    }

    public function approve(User $user, PayrollPeriod $payrollPeriod): bool
    {
        if (!$this->hasPermission($user, 'approve payroll')) {
            return false;
        }

        return $payrollPeriod->canBeApproved();
    }

    public function process(User $user, PayrollPeriod $payrollPeriod): bool
    {
        if (!$this->hasPermission($user, 'process payroll')) {
            return false;
        }

        return $payrollPeriod->canStartProcessing();
    }

    public function markPaid(User $user, PayrollPeriod $payrollPeriod): bool
    {
        if (!$this->hasPermission($user, 'process payroll')) {
            return false;
        }

        return $payrollPeriod->canBeMarkedAsPaid();
    }

    public function cancel(User $user, PayrollPeriod $payrollPeriod): bool
    {
        if (!$this->hasPermission($user, 'cancel payroll')) {
            return false;
        }

        return $payrollPeriod->canBeCancelled();
    }
}
