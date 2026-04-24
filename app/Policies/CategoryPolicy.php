<?php

namespace App\Policies;

use App\Models\Inventory\Category;
use App\Models\User;
use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'categories';

    public function delete(User $user, Category $category): bool
    {
        if (! $user->can("delete {$this->resource}")) {
            return false;
        }

        // Cannot delete category with products or children.
        return $category->products()->count() === 0
            && $category->children()->count() === 0;
    }
}
