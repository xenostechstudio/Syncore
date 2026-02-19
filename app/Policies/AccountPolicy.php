<?php

namespace App\Policies;

use App\Models\Accounting\Account;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view accounts');
    }

    public function view(User $user, Account $account): bool
    {
        return $this->checkView($user, 'view accounts');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create accounts');
    }

    public function update(User $user, Account $account): bool
    {
        if (!$this->hasPermission($user, 'edit accounts')) {
            return false;
        }

        // System accounts cannot be edited
        return !$account->is_system;
    }

    public function delete(User $user, Account $account): bool
    {
        if (!$this->hasPermission($user, 'delete accounts')) {
            return false;
        }

        // Cannot delete system accounts or accounts with transactions
        if ($account->is_system) {
            return false;
        }

        return $account->journalLines()->count() === 0;
    }
}
