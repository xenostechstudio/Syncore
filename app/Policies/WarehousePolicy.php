<?php

namespace App\Policies;

use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view warehouses');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $this->checkView($user, 'view warehouses');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create warehouses');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $this->hasPermission($user, 'edit warehouses');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        if (!$this->hasPermission($user, 'delete warehouses')) {
            return false;
        }

        // Cannot delete warehouse with stock
        return $warehouse->stocks()->where('quantity', '>', 0)->count() === 0;
    }
}
