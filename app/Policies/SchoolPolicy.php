<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use App\Traits\PolicyTrait;
use Illuminate\Auth\Access\Response;

class SchoolPolicy
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
    public function view(User $user, School $school): bool
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
    public function update(User $user, School $school): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, School $school): bool
    {
        return $this->isSuperadmin($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    public function restore(User $user, School $school): bool
    {
        return $this->isSuperadmin($user);
    }

    public function forceDelete(User $user, School $school): bool
    {
        return $this->isSuperadmin($user);
    }
}