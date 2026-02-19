<?php

namespace App\Policies;

use App\Models\Inventory\Product;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view products');
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can('view products');
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->can('create products');
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('edit products');
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        if (!$user->can('delete products')) {
            return false;
        }

        // Prevent deletion if product has stock or is used in orders
        if ($product->stocks()->where('quantity', '>', 0)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can adjust stock.
     */
    public function adjustStock(User $user, Product $product): bool
    {
        return $user->can('adjust inventory');
    }

    /**
     * Determine whether the user can view stock levels.
     */
    public function viewStock(User $user, Product $product): bool
    {
        return $user->can('view inventory');
    }

    /**
     * Determine whether the user can export products.
     */
    public function export(User $user): bool
    {
        return $user->can('export products');
    }

    /**
     * Determine whether the user can import products.
     */
    public function import(User $user): bool
    {
        return $user->can('import products');
    }
}
