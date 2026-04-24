<?php

namespace App\Policies;

use App\Models\HR\Position;
use App\Models\User;
use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class PositionPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'positions';

    public function delete(User $user, Position $position): bool
    {
        if (! $user->can("delete {$this->resource}")) {
            return false;
        }

        return $position->employees()->count() === 0;
    }
}
