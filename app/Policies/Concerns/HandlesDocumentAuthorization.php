<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared authorization logic for document-based policies.
 * 
 * Provides common patterns for checking permissions on documents
 * that have states (draft, confirmed, cancelled, etc.)
 */
trait HandlesDocumentAuthorization
{
    /**
     * Check if user has the given permission.
     */
    protected function hasPermission(User $user, string $permission): bool
    {
        return $user->can($permission);
    }

    /**
     * Check if document can be edited based on its state.
     */
    protected function canEditDocument(Model $document): bool
    {
        if (method_exists($document, 'state') || property_exists($document, 'state')) {
            $state = $document->state;
            if (method_exists($state, 'canEdit')) {
                return $state->canEdit();
            }
        }

        // Fallback: check for common editable statuses
        $status = $document->status ?? null;
        return in_array($status, ['draft', 'new', 'rfq', 'pending']);
    }

    /**
     * Check if document can be deleted based on its state.
     */
    protected function canDeleteDocument(Model $document): bool
    {
        if (method_exists($document, 'state') || property_exists($document, 'state')) {
            $state = $document->state;
            if (method_exists($state, 'isTerminal')) {
                return !$state->isTerminal();
            }
        }

        // Fallback: check for common deletable statuses
        $status = $document->status ?? null;
        return in_array($status, ['draft', 'new', 'cancelled', 'rfq', 'pending']);
    }

    /**
     * Check if document can be cancelled based on its state.
     */
    protected function canCancelDocument(Model $document): bool
    {
        if (method_exists($document, 'state') || property_exists($document, 'state')) {
            $state = $document->state;
            if (method_exists($state, 'canCancel')) {
                return $state->canCancel();
            }
        }

        // Fallback: check for common cancellable statuses
        $status = $document->status ?? null;
        return !in_array($status, ['cancelled', 'paid', 'completed', 'delivered']);
    }

    /**
     * Check if document is in a terminal state.
     */
    protected function isTerminal(Model $document): bool
    {
        if (method_exists($document, 'state') || property_exists($document, 'state')) {
            $state = $document->state;
            if (method_exists($state, 'isTerminal')) {
                return $state->isTerminal();
            }
        }

        $status = $document->status ?? null;
        return in_array($status, ['cancelled', 'paid', 'completed', 'delivered', 'billed']);
    }

    /**
     * Standard viewAny check.
     */
    protected function checkViewAny(User $user, string $permission): bool
    {
        return $this->hasPermission($user, $permission);
    }

    /**
     * Standard view check.
     */
    protected function checkView(User $user, string $permission): bool
    {
        return $this->hasPermission($user, $permission);
    }

    /**
     * Standard create check.
     */
    protected function checkCreate(User $user, string $permission): bool
    {
        return $this->hasPermission($user, $permission);
    }

    /**
     * Standard update check with state validation.
     */
    protected function checkUpdate(User $user, Model $document, string $permission): bool
    {
        if (!$this->hasPermission($user, $permission)) {
            return false;
        }

        return $this->canEditDocument($document);
    }

    /**
     * Standard delete check with state validation.
     */
    protected function checkDelete(User $user, Model $document, string $permission): bool
    {
        if (!$this->hasPermission($user, $permission)) {
            return false;
        }

        return $this->canDeleteDocument($document);
    }

    /**
     * Standard cancel check with state validation.
     */
    protected function checkCancel(User $user, Model $document, string $permission): bool
    {
        if (!$this->hasPermission($user, $permission)) {
            return false;
        }

        return $this->canCancelDocument($document);
    }
}
