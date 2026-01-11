<?php

namespace App\Livewire\Concerns;

trait WithPermissions
{
    /**
     * Check if the current user has a specific permission.
     */
    protected function can(string $permission): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admin can do everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->can($permission);
    }

    /**
     * Check if user has module access.
     */
    protected function canAccessModule(string $module): bool
    {
        return $this->can("access.{$module}");
    }

    /**
     * Check if user can perform action on module.
     */
    protected function canPerform(string $module, string $action): bool
    {
        return $this->can("{$module}.{$action}");
    }

    /**
     * Authorize an action or abort.
     */
    protected function authorize(string $permission, ?string $message = null): void
    {
        if (!$this->can($permission)) {
            abort(403, $message ?? "You don't have permission to perform this action.");
        }
    }

    /**
     * Get permissions for the current module (for UI display).
     */
    protected function getModulePermissions(string $module): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        if ($user->hasRole('super-admin')) {
            return [
                'access' => true,
                'view' => true,
                'create' => true,
                'edit' => true,
                'delete' => true,
                'export' => true,
            ];
        }

        return [
            'access' => $user->can("access.{$module}"),
            'view' => $user->can("{$module}.view"),
            'create' => $user->can("{$module}.create"),
            'edit' => $user->can("{$module}.edit"),
            'delete' => $user->can("{$module}.delete"),
            'export' => $user->can("{$module}.export"),
        ];
    }
}
