<?php

namespace App\Policies;

use App\Models\HR\Department;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkViewAny($user, 'view departments');
    }

    public function view(User $user, Department $department): bool
    {
        return $this->checkView($user, 'view departments');
    }

    public function create(User $user): bool
    {
        return $this->checkCreate($user, 'create departments');
    }

    public function update(User $user, Department $department): bool
    {
        return $this->hasPermission($user, 'edit departments');
    }

    public function delete(User $user, Department $department): bool
    {
        if (!$this->hasPermission($user, 'delete departments')) {
            return false;
        }

        // Cannot delete department with employees or children
        if ($department->employees()->count() > 0) {
            return false;
        }

        return $department->children()->count() === 0;
    }
}
