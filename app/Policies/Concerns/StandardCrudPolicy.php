<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Default CRUD authorization stubs for resource-based policies.
 *
 * Subclasses set a `$resource` property (e.g. 'taxes') and get the
 * standard view/create/update/delete checks against permissions named
 * 'view taxes', 'create taxes', 'edit taxes', 'delete taxes'.
 *
 * Override any method for non-standard behavior (e.g. extra guards on
 * delete).
 */
trait StandardCrudPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("view {$this->resource}");
    }

    public function view(User $user, $model): bool
    {
        return $user->can("view {$this->resource}");
    }

    public function create(User $user): bool
    {
        return $user->can("create {$this->resource}");
    }

    public function update(User $user, $model): bool
    {
        return $user->can("edit {$this->resource}");
    }

    public function delete(User $user, $model): bool
    {
        return $user->can("delete {$this->resource}");
    }
}
