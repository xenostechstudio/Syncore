<?php

namespace App\Policies;

use App\Models\Sales\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view customers');
    }

    /**
     * Determine whether the user can view the customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        return $user->can('view customers');
    }

    /**
     * Determine whether the user can create customers.
     */
    public function create(User $user): bool
    {
        return $user->can('create customers');
    }

    /**
     * Determine whether the user can update the customer.
     */
    public function update(User $user, Customer $customer): bool
    {
        return $user->can('edit customers');
    }

    /**
     * Determine whether the user can delete the customer.
     */
    public function delete(User $user, Customer $customer): bool
    {
        if (!$user->can('delete customers')) {
            return false;
        }

        // Prevent deletion if customer has orders or invoices
        if ($customer->salesOrders()->exists() || $customer->invoices()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view customer's financial data.
     */
    public function viewFinancials(User $user, Customer $customer): bool
    {
        return $user->can('view customer financials');
    }

    /**
     * Determine whether the user can export customers.
     */
    public function export(User $user): bool
    {
        return $user->can('export customers');
    }

    /**
     * Determine whether the user can import customers.
     */
    public function import(User $user): bool
    {
        return $user->can('import customers');
    }
}
