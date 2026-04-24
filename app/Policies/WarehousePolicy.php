<?php

namespace App\Policies;

use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'warehouses';

    public function delete(User $user, Warehouse $warehouse): bool
    {
        if (! $user->can("delete {$this->resource}")) {
            return false;
        }

        return $warehouse->stocks()->where('quantity', '>', 0)->count() === 0;
    }
}
