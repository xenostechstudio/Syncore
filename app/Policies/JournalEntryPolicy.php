<?php

namespace App\Policies;

use App\Models\Accounting\JournalEntry;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class JournalEntryPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view journal entries');
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $this->checkView($user, 'view journal entries');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create journal entries');
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        if (!$this->hasPermission($user, 'edit journal entries')) {
            return false;
        }

        return $journalEntry->status === 'draft';
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        if (!$this->hasPermission($user, 'delete journal entries')) {
            return false;
        }

        return $journalEntry->status === 'draft';
    }

    public function post(User $user, JournalEntry $journalEntry): bool
    {
        if (!$this->hasPermission($user, 'post journal entries')) {
            return false;
        }

        return $journalEntry->status === 'draft' && $journalEntry->isBalanced();
    }

    public function reverse(User $user, JournalEntry $journalEntry): bool
    {
        if (!$this->hasPermission($user, 'reverse journal entries')) {
            return false;
        }

        return $journalEntry->status === 'posted';
    }
}
