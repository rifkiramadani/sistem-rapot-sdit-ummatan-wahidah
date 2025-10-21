<?php

namespace App\Policies;

use App\Models\SchoolAcademicYear;
use App\Models\User;
use App\Traits\PolicyTrait;

class SchoolAcademicYearPolicy
{
    use PolicyTrait;

    /**
     * Who can view/access this academic year's dashboard?
     */
    public function view(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        // 1. Management can view all.
        if ($this->isManagement($user)) {
            return true;
        }

        // 2. A teacher is allowed, AS LONG AS they are registered in that academic year.
        if ($this->isTeacher($user)) {
            // Assumption: `teachers` relationship exists on User model: hasMany(Teacher::class, 'user_id')
            return $user->teacher()->where('school_academic_year_id', $schoolAcademicYear->id)->exists();
        }

        return false;
    }

    /**
     * Who can view the list of school academic years?
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }

    /**
     * Who can create new school academic years?
     */
    public function create(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }

    /**
     * Who can update school academic years?
     */
    public function update(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }

    /**
     * Who can delete school academic years?
     */
    public function delete(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }

    /**
     * Who can restore school academic years?
     */
    public function restore(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }

    /**
     * Who can permanently delete school academic years?
     */
    public function forceDelete(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }
}