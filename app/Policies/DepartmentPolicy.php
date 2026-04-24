<?php

namespace App\Policies;

use App\Models\HR\Department;
use App\Models\User;
use App\Policies\Concerns\StandardCrudPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization, StandardCrudPolicy;

    protected string $resource = 'departments';

    public function delete(User $user, Department $department): bool
    {
        if (! $user->can("delete {$this->resource}")) {
            return false;
        }

        // Cannot delete department with employees or child departments.
        return $department->employees()->count() === 0
            && $department->children()->count() === 0;
    }
}
