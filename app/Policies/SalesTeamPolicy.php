<?php

namespace App\Policies;

use App\Models\Sales\SalesTeam;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesTeamPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view sales teams');
    }

    public function view(User $user, SalesTeam $salesTeam): bool
    {
        return $this->checkView($user, 'view sales teams');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create sales teams');
    }

    public function update(User $user, SalesTeam $salesTeam): bool
    {
        return $this->hasPermission($user, 'edit sales teams');
    }

    public function delete(User $user, SalesTeam $salesTeam): bool
    {
        return $this->hasPermission($user, 'delete sales teams');
    }
}
