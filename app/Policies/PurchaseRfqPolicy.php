<?php

namespace App\Policies;

use App\Models\Purchase\PurchaseRfq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseRfqPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view purchase orders');
    }

    public function view(User $user, PurchaseRfq $rfq): bool
    {
        return $user->can('view purchase orders');
    }

    public function create(User $user): bool
    {
        return $user->can('create purchase orders');
    }

    public function update(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('edit purchase orders')) {
            return false;
        }

        return $rfq->state->canEdit();
    }

    public function delete(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('delete purchase orders')) {
            return false;
        }

        return in_array($rfq->status, ['rfq', 'sent']);
    }

    public function sendRfq(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('send purchase orders')) {
            return false;
        }

        return $rfq->state->canSendRfq();
    }

    public function confirm(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('confirm purchase orders')) {
            return false;
        }

        return $rfq->state->canConfirmOrder();
    }

    public function receive(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('receive purchase orders')) {
            return false;
        }

        return $rfq->state->canReceive();
    }

    public function createBill(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('create vendor bills')) {
            return false;
        }

        return $rfq->state->canCreateBill();
    }

    public function cancel(User $user, PurchaseRfq $rfq): bool
    {
        if (!$user->can('cancel purchase orders')) {
            return false;
        }

        return $rfq->state->canCancel();
    }
}
