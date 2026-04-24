<?php

namespace App\Policies;

use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'taxes';
}
