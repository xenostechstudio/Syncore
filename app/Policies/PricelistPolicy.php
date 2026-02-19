<?php

namespace App\Policies;

use App\Models\Sales\Pricelist;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class PricelistPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view pricelists');
    }

    public function view(User $user, Pricelist $pricelist): bool
    {
        return $this->checkView($user, 'view pricelists');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create pricelists');
    }

    public function update(User $user, Pricelist $pricelist): bool
    {
        return $this->hasPermission($user, 'edit pricelists');
    }

    public function delete(User $user, Pricelist $pricelist): bool
    {
        return $this->hasPermission($user, 'delete pricelists');
    }
}
