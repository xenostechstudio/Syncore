<?php

namespace App\Policies;

use App\Models\Inventory\InventoryTransfer;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryTransferPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view inventory transfers');
    }

    public function view(User $user, InventoryTransfer $transfer): bool
    {
        return $this->checkView($user, 'view inventory transfers');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create inventory transfers');
    }

    public function update(User $user, InventoryTransfer $transfer): bool
    {
        if (!$this->hasPermission($user, 'edit inventory transfers')) {
            return false;
        }

        return $transfer->state->canEdit();
    }

    public function delete(User $user, InventoryTransfer $transfer): bool
    {
        if (!$this->hasPermission($user, 'delete inventory transfers')) {
            return false;
        }

        return $transfer->state->canEdit();
    }

    public function process(User $user, InventoryTransfer $transfer): bool
    {
        if (!$this->hasPermission($user, 'process inventory transfers')) {
            return false;
        }

        return !$transfer->state->isTerminal();
    }

    public function cancel(User $user, InventoryTransfer $transfer): bool
    {
        if (!$this->hasPermission($user, 'cancel inventory transfers')) {
            return false;
        }

        return $transfer->state->canCancel();
    }
}
