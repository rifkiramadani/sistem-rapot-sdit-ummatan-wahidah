<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\User;
use App\Traits\PolicyTrait;

class ClassroomSubjectPolicy
{
    use PolicyTrait;

    /**
     * Who can view the list of classroom subjects?
     * Management and Teachers can view the list page, but teachers will see filtered data.
     */
    public function viewAny(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can view the details of a classroom subject?
     */
    public function view(User $user, ClassroomSubject $classroomSubject): bool
    {
        return $classroomSubject->canBeManagedBy($user);
    }

    /**
     * Who can create classroom subjects? Management and Teachers (in their classrooms)
     */
    public function create(User $user): bool
    {
        // This will be checked at the controller level with the specific classroom
        // For now, allow management and teachers to see the create option
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can update classroom subjects?
     * Management is allowed, or the Teacher who manages that classroom.
     */
    public function update(User $user, ClassroomSubject $classroomSubject): bool
    {
        return $classroomSubject->canBeManagedBy($user);
    }

    /**
     * Who can delete classroom subjects? Management and Teachers (in their classrooms)
     */
    public function delete(User $user, ClassroomSubject $classroomSubject): bool
    {
        return $classroomSubject->canBeManagedBy($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function restore(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function forceDelete(User $user): bool
    {
        return $this->isManagement($user);
    }
}