<?php

namespace App\Policies;

use App\Models\Purchase\Supplier;
use App\Models\User;
use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'suppliers';

    public function delete(User $user, Supplier $supplier): bool
    {
        if (! $user->can("delete {$this->resource}")) {
            return false;
        }

        // Cannot delete supplier with purchase orders or vendor bills.
        return $supplier->purchaseOrders()->count() === 0
            && $supplier->vendorBills()->count() === 0;
    }
}
