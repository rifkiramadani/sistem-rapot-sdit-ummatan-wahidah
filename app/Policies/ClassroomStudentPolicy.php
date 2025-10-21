<?php

namespace App\Policies;

use App\Models\ClassroomStudent;
use App\Models\User;
use App\Traits\PolicyTrait;

class ClassroomStudentPolicy
{
    use PolicyTrait;

    /**
     * Who can view the classroom student list?
     * Management and Teachers can view, but teachers will see filtered data based on their classrooms.
     */
    public function viewAny(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can view a specific classroom student?
     */
    public function view(User $user, ClassroomStudent $classroomStudent): bool
    {
        return $classroomStudent->canBeManagedBy($user);
    }

    /**
     * Who can create new classroom students?
     * Management and Teachers who manage the classroom can add students.
     */
    public function create(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can update a classroom student?
     * This typically applies to editing student assignments in classrooms.
     */
    public function update(User $user, ClassroomStudent $classroomStudent): bool
    {
        return $classroomStudent->canBeManagedBy($user);
    }

    /**
     * Who can delete a classroom student?
     */
    public function delete(User $user, ClassroomStudent $classroomStudent): bool
    {
        return $classroomStudent->canBeManagedBy($user);
    }

    /**
     * Who can bulk delete classroom students?
     */
    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can view student summatives?
     */
    public function viewSummatives(User $user, ClassroomStudent $classroomStudent): bool
    {
        return $classroomStudent->canBeManagedBy($user);
    }

    /**
     * Who can export student documents?
     */
    public function exportDocuments(User $user, ClassroomStudent $classroomStudent): bool
    {
        return $classroomStudent->canBeManagedBy($user);
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