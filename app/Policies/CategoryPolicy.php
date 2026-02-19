<?php

namespace App\Policies;

use App\Models\Inventory\Category;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view categories');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->checkView($user, 'view categories');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create categories');
    }

    public function update(User $user, Category $category): bool
    {
        return $this->hasPermission($user, 'edit categories');
    }

    public function delete(User $user, Category $category): bool
    {
        if (!$this->hasPermission($user, 'delete categories')) {
            return false;
        }

        // Cannot delete category with products or children
        if ($category->products()->count() > 0) {
            return false;
        }

        return $category->children()->count() === 0;
    }
}
