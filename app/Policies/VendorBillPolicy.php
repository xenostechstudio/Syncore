<?php

namespace App\Policies;

use App\Models\Purchase\VendorBill;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class VendorBillPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view vendor bills');
    }

    public function view(User $user, VendorBill $bill): bool
    {
        return $user->can('view vendor bills');
    }

    public function create(User $user): bool
    {
        return $user->can('create vendor bills');
    }

    public function update(User $user, VendorBill $bill): bool
    {
        if (!$user->can('edit vendor bills')) {
            return false;
        }

        return $bill->state->canEdit();
    }

    public function delete(User $user, VendorBill $bill): bool
    {
        if (!$user->can('delete vendor bills')) {
            return false;
        }

        return in_array($bill->status, ['draft', 'cancelled']);
    }

    public function confirm(User $user, VendorBill $bill): bool
    {
        if (!$user->can('confirm vendor bills')) {
            return false;
        }

        return $bill->state->canConfirm();
    }

    public function registerPayment(User $user, VendorBill $bill): bool
    {
        if (!$user->can('register vendor bill payments')) {
            return false;
        }

        return $bill->state->canRegisterPayment();
    }

    public function cancel(User $user, VendorBill $bill): bool
    {
        if (!$user->can('cancel vendor bills')) {
            return false;
        }

        return $bill->state->canCancel();
    }
}
