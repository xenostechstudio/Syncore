<?php

namespace App\Policies;

use App\Models\CRM\Lead;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view leads');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->checkView($user, 'view leads');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create leads');
    }

    public function update(User $user, Lead $lead): bool
    {
        if (!$this->hasPermission($user, 'edit leads')) {
            return false;
        }

        // Cannot edit converted or lost leads
        return !in_array($lead->status, ['converted', 'lost']);
    }

    public function delete(User $user, Lead $lead): bool
    {
        if (!$this->hasPermission($user, 'delete leads')) {
            return false;
        }

        // Cannot delete converted leads
        return $lead->status !== 'converted';
    }

    public function convert(User $user, Lead $lead): bool
    {
        if (!$this->hasPermission($user, 'convert leads')) {
            return false;
        }

        return in_array($lead->status, ['qualified', 'new', 'contacted']);
    }

    public function markLost(User $user, Lead $lead): bool
    {
        if (!$this->hasPermission($user, 'edit leads')) {
            return false;
        }

        return !in_array($lead->status, ['converted', 'lost']);
    }
}
