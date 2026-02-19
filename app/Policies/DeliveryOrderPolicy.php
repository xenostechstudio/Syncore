<?php

namespace App\Policies;

use App\Models\Delivery\DeliveryOrder;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryOrderPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view delivery orders');
    }

    public function view(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->can('view delivery orders');
    }

    public function create(User $user): bool
    {
        return $user->can('create delivery orders');
    }

    public function update(User $user, DeliveryOrder $deliveryOrder): bool
    {
        if (!$user->can('edit delivery orders')) {
            return false;
        }

        return !$deliveryOrder->status->isTerminal();
    }

    public function delete(User $user, DeliveryOrder $deliveryOrder): bool
    {
        if (!$user->can('delete delivery orders')) {
            return false;
        }

        return $deliveryOrder->status->value === 'pending';
    }

    public function transition(User $user, DeliveryOrder $deliveryOrder): bool
    {
        if (!$user->can('process delivery orders')) {
            return false;
        }

        return !$deliveryOrder->status->isTerminal();
    }

    public function cancel(User $user, DeliveryOrder $deliveryOrder): bool
    {
        if (!$user->can('cancel delivery orders')) {
            return false;
        }

        return !$deliveryOrder->status->isTerminal();
    }
}
