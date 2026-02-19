<?php

namespace App\Policies;

use App\Models\HR\Position;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class PositionPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view positions');
    }

    public function view(User $user, Position $position): bool
    {
        return $this->checkView($user, 'view positions');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create positions');
    }

    public function update(User $user, Position $position): bool
    {
        return $this->hasPermission($user, 'edit positions');
    }

    public function delete(User $user, Position $position): bool
    {
        if (!$this->hasPermission($user, 'delete positions')) {
            return false;
        }

        // Cannot delete position with employees
        return $position->employees()->count() === 0;
    }
}
