<?php

namespace App\Policies;

use App\Models\Sales\Tax;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view taxes');
    }

    public function view(User $user, Tax $tax): bool
    {
        return $this->checkView($user, 'view taxes');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create taxes');
    }

    public function update(User $user, Tax $tax): bool
    {
        return $this->hasPermission($user, 'edit taxes');
    }

    public function delete(User $user, Tax $tax): bool
    {
        return $this->hasPermission($user, 'delete taxes');
    }
}
