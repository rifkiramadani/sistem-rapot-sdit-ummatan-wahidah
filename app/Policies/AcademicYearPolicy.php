<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;
use App\Traits\PolicyTrait;
use Illuminate\Auth\Access\Response;

class AcademicYearPolicy
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
    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AcademicYear $academicYear): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AcademicYear $academicYear): bool
    {
        return $this->isSuperAdmin($user);
    }
}
