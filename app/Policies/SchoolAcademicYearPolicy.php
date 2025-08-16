<?php

namespace App\Policies;

use App\Models\SchoolAcademicYear;
use App\Models\User;
use App\Traits\PolicyTrait;
use Illuminate\Auth\Access\Response;

class SchoolAcademicYearPolicy
{
    use PolicyTrait;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        return $this->isSuperadmin($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        return $this->isSuperadmin($user);
    }
}
