<?php

namespace App\Policies;

use App\Models\Invoicing\Invoice;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view invoices');
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('view invoices');
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $user->can('create invoices');
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        if (!$user->can('edit invoices')) {
            return false;
        }

        // Can only edit draft invoices
        return $invoice->state->canEdit();
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if (!$user->can('delete invoices')) {
            return false;
        }

        // Can only delete draft or cancelled invoices
        return in_array($invoice->status, ['draft', 'cancelled']);
    }

    /**
     * Determine whether the user can send the invoice.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        if (!$user->can('send invoices')) {
            return false;
        }

        return $invoice->state->canSend();
    }

    /**
     * Determine whether the user can register a payment.
     */
    public function registerPayment(User $user, Invoice $invoice): bool
    {
        if (!$user->can('register payments')) {
            return false;
        }

        return $invoice->state->canRegisterPayment();
    }

    /**
     * Determine whether the user can cancel the invoice.
     */
    public function cancel(User $user, Invoice $invoice): bool
    {
        if (!$user->can('cancel invoices')) {
            return false;
        }

        return $invoice->state->canCancel();
    }
}
