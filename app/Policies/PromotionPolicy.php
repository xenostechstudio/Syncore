<?php

namespace App\Policies;

use App\Models\Sales\Promotion;
use App\Models\User;
use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'promotions';

    public function delete(User $user, Promotion $promotion): bool
    {
        if (! $user->can("delete {$this->resource}")) {
            return false;
        }

        // Cannot delete promotion that has been used.
        return $promotion->usage_count === 0;
    }
}
