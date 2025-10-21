<?php

namespace App\Policies;

use App\Models\Summative;
use App\Models\User;
use App\Traits\PolicyTrait;

class SummativePolicy
{
    use PolicyTrait;

    /**
     * Who can view the summative list?
     * Management and Teachers can view, but teachers will see filtered data based on their classrooms.
     */
    public function viewAny(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can view a specific summative?
     */
    public function view(User $user, Summative $summative): bool
    {
        // Management can view all summatives
        if ($this->isManagement($user)) {
            return true;
        }

        // Teachers can only view summatives in their assigned classrooms
        if ($this->isTeacher($user)) {
            return $this->isTeacherOfClassroom($user, $summative->classroomSubject->classroom);
        }

        return false;
    }

    /**
     * Who can create new summatives?
     * Management and Teachers who manage the classroom can create summatives.
     */
    public function create(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can update a summative?
     * Teachers can fully manage summatives in their assigned classrooms.
     */
    public function update(User $user, Summative $summative): bool
    {
        // Management can update all summatives
        if ($this->isManagement($user)) {
            return true;
        }

        // Teachers can fully manage summatives in their assigned classrooms
        if ($this->isTeacher($user)) {
            return $this->isTeacherOfClassroom($user, $summative->classroomSubject->classroom);
        }

        return false;
    }

    /**
     * Who can delete a summative?
     * Teachers can fully manage summatives in their assigned classrooms.
     */
    public function delete(User $user, Summative $summative): bool
    {
        // Management can delete all summatives
        if ($this->isManagement($user)) {
            return true;
        }

        // Teachers can fully manage summatives in their assigned classrooms
        if ($this->isTeacher($user)) {
            return $this->isTeacherOfClassroom($user, $summative->classroomSubject->classroom);
        }

        return false;
    }

    /**
     * Who can bulk delete summatives?
     */
    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can update summative values?
     */
    public function updateValues(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can export summative documents?
     */
    public function exportDocuments(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Helper method to check if teacher is assigned to the classroom
     */
    private function isTeacherOfClassroom(User $user, $classroom): bool
    {
        if (!$classroom) {
            return false;
        }

        // Get the teacher data for this user IN THIS classroom's ACADEMIC YEAR
        $teacherRecord = $user->teacher()
                             ->where('school_academic_year_id', $classroom->school_academic_year_id)
                             ->first();

        // If this user is not a teacher in this academic year, deny
        if (!$teacherRecord) {
            return false;
        }

        // Allow ONLY if the logged-in teacher's ID = the homeroom teacher's ID
        return $teacherRecord->id === $classroom->teacher_id;
    }
}