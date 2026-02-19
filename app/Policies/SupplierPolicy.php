<?php

namespace App\Policies;

use App\Models\Purchase\Supplier;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view suppliers');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $this->checkView($user, 'view suppliers');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create suppliers');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->hasPermission($user, 'edit suppliers');
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        if (!$this->hasPermission($user, 'delete suppliers')) {
            return false;
        }

        // Cannot delete supplier with purchase orders or bills
        if ($supplier->purchaseOrders()->count() > 0) {
            return false;
        }

        return $supplier->vendorBills()->count() === 0;
    }
}
