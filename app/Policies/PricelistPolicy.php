<?php

namespace App\Policies;

use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class PricelistPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'pricelists';
}
