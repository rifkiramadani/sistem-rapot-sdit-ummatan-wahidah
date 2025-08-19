<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentSummative;
use App\Models\Summative;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSummativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->warn('No students found to assign scores.');
            return;
        }

        // Iterate over each student to assign their scores
        foreach ($students as $student) {
            // 1. Find the student's classroom. We'll take the first one assigned.
            $classroomStudent = $student->classroomStudents()->first();

            if (!$classroomStudent) {
                continue; // Skip if the student isn't in a class.
            }

            // 2. Get all subject IDs for that specific classroom.
            $subjectIdsInClass = DB::table('class_subjects')
                ->where('classroom_id', $classroomStudent->classroom_id)
                ->pluck('subject_id');

            if ($subjectIdsInClass->isEmpty()) {
                continue; // Skip if the class has no subjects.
            }

            // 3. Find summatives that are for the class subjects AND match the student's school year.
            $relevantSummatives = Summative::whereIn('subject_id', $subjectIdsInClass)
                ->whereHas('summativeType', function ($query) use ($student) {
                    $query->where('school_academic_year_id', $student->school_academic_year_id);
                })
                ->get();

            // 4. Create a score record for the student for each relevant summative.
            foreach ($relevantSummatives as $summative) {
                StudentSummative::factory()->create([
                    'student_id' => $student->id,
                    'summative_id' => $summative->id,
                ]);
            }
        }

        $this->command->info('Assigned scores to students based on their class subjects.');
    }
}
