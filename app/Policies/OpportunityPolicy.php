<?php

namespace App\Policies;

use App\Models\CRM\Opportunity;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpportunityPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view opportunities');
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return $this->checkView($user, 'view opportunities');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create opportunities');
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        if (!$this->hasPermission($user, 'edit opportunities')) {
            return false;
        }

        return $opportunity->isOpen();
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        if (!$this->hasPermission($user, 'delete opportunities')) {
            return false;
        }

        // Cannot delete won opportunities with sales orders
        if ($opportunity->isWon() && $opportunity->sales_order_id) {
            return false;
        }

        return true;
    }

    public function markWon(User $user, Opportunity $opportunity): bool
    {
        if (!$this->hasPermission($user, 'close opportunities')) {
            return false;
        }

        return $opportunity->isOpen();
    }

    public function markLost(User $user, Opportunity $opportunity): bool
    {
        if (!$this->hasPermission($user, 'close opportunities')) {
            return false;
        }

        return $opportunity->isOpen();
    }

    public function changeStage(User $user, Opportunity $opportunity): bool
    {
        if (!$this->hasPermission($user, 'edit opportunities')) {
            return false;
        }

        return $opportunity->isOpen();
    }
}
