<?php

namespace App\Policies;

use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesOrderPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    /**
     * Determine whether the user can view any sales orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view sales orders');
    }

    /**
     * Determine whether the user can view the sales order.
     */
    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('view sales orders');
    }

    /**
     * Determine whether the user can create sales orders.
     */
    public function create(User $user): bool
    {
        return $user->can('create sales orders');
    }

    /**
     * Determine whether the user can update the sales order.
     */
    public function update(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('edit sales orders')) {
            return false;
        }

        return $salesOrder->state->canEdit();
    }

    /**
     * Determine whether the user can delete the sales order.
     */
    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('delete sales orders')) {
            return false;
        }

        // Can only delete quotations that aren't locked
        return !$salesOrder->isLocked() && !$salesOrder->state->isTerminal();
    }

    /**
     * Determine whether the user can confirm the sales order.
     */
    public function confirm(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('confirm sales orders')) {
            return false;
        }

        return $salesOrder->state->canConfirm();
    }

    /**
     * Determine whether the user can create an invoice from the sales order.
     */
    public function createInvoice(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('create invoices')) {
            return false;
        }

        return $salesOrder->state->canCreateInvoice() && $salesOrder->hasQuantityToInvoice();
    }

    /**
     * Determine whether the user can create a delivery order.
     */
    public function createDeliveryOrder(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('create delivery orders')) {
            return false;
        }

        return $salesOrder->canCreateDeliveryOrder();
    }

    /**
     * Determine whether the user can cancel the sales order.
     */
    public function cancel(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('cancel sales orders')) {
            return false;
        }

        return $salesOrder->state->canCancel();
    }

    /**
     * Determine whether the user can edit items on the sales order.
     */
    public function editItems(User $user, SalesOrder $salesOrder): bool
    {
        if (!$user->can('edit sales orders')) {
            return false;
        }

        return $salesOrder->canEditItems();
    }
}
