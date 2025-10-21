<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;
use App\Traits\PolicyTrait;

class SubjectPolicy
{
    use PolicyTrait;

    // Only management can manage subject master data

    public function viewAny(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function view(User $user, Subject $subject): bool
    {
        return $this->isManagement($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function update(User $user, Subject $subject): bool
    {
        return $this->isManagement($user);
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $this->isManagement($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function restore(User $user, Subject $subject): bool
    {
        return $this->isManagement($user);
    }

    public function forceDelete(User $user, Subject $subject): bool
    {
        return $this->isManagement($user);
    }
}