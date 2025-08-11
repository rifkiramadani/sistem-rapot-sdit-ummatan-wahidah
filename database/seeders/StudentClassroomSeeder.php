<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassroom;
use Illuminate\Database\Seeder;

class StudentClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Group students and classrooms by their school_academic_year_id
        $studentsBySchoolYear = Student::all()->groupBy('school_academic_year_id');
        $classroomsBySchoolYear = Classroom::all()->groupBy('school_academic_year_id');

        if ($studentsBySchoolYear->isEmpty() || $classroomsBySchoolYear->isEmpty()) {
            $this->command->warn('No students or classrooms found to assign.');
            return;
        }

        // 2. Iterate over each school year group
        foreach ($studentsBySchoolYear as $schoolYearId => $students) {
            // Find the corresponding classrooms for this specific school year
            $classrooms = $classroomsBySchoolYear->get($schoolYearId);

            // Skip if there are no classrooms for this school year
            if (is_null($classrooms) || $classrooms->isEmpty()) {
                continue;
            }

            // 3. Distribute the students of this school year into the classrooms of the same school year
            $classroomIndex = 0;
            foreach ($students as $student) {
                $classroom = $classrooms[$classroomIndex];

                StudentClassroom::firstOrCreate([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                ]);

                // Move to the next classroom in this group
                $classroomIndex = ($classroomIndex + 1) % $classrooms->count();
            }
        }

        $this->command->info('Assigned all students to classrooms within the correct school year.');
    }
}
