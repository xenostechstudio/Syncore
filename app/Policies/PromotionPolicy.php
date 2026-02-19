<?php

namespace App\Policies;

use App\Models\Sales\Promotion;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view promotions');
    }

    public function view(User $user, Promotion $promotion): bool
    {
        return $this->checkView($user, 'view promotions');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create promotions');
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $this->hasPermission($user, 'edit promotions');
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        if (!$this->hasPermission($user, 'delete promotions')) {
            return false;
        }

        // Cannot delete promotion that has been used
        return $promotion->usage_count === 0;
    }
}
