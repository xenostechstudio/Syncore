<?php

namespace App\Policies;

use App\Models\Inventory\InventoryAdjustment;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryAdjustmentPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view inventory adjustments');
    }

    public function view(User $user, InventoryAdjustment $adjustment): bool
    {
        return $this->checkView($user, 'view inventory adjustments');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create inventory adjustments');
    }

    public function update(User $user, InventoryAdjustment $adjustment): bool
    {
        if (!$this->hasPermission($user, 'edit inventory adjustments')) {
            return false;
        }

        return $adjustment->state->canEdit();
    }

    public function delete(User $user, InventoryAdjustment $adjustment): bool
    {
        if (!$this->hasPermission($user, 'delete inventory adjustments')) {
            return false;
        }

        return $adjustment->state->canEdit();
    }

    public function post(User $user, InventoryAdjustment $adjustment): bool
    {
        if (!$this->hasPermission($user, 'post inventory adjustments')) {
            return false;
        }

        return $adjustment->state->canValidate();
    }

    public function cancel(User $user, InventoryAdjustment $adjustment): bool
    {
        if (!$this->hasPermission($user, 'cancel inventory adjustments')) {
            return false;
        }

        return $adjustment->state->canCancel();
    }
}
