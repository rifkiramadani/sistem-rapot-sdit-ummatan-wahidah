<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Group classrooms and subjects by their school_academic_year_id
        $classroomsBySchoolYear = Classroom::all()->groupBy('school_academic_year_id');
        $subjectsBySchoolYear = Subject::all()->groupBy('school_academic_year_id');

        if ($classroomsBySchoolYear->isEmpty() || $subjectsBySchoolYear->isEmpty()) {
            $this->command->warn('No classrooms or subjects found to assign.');
            return;
        }

        // 2. Iterate over each school year group of classrooms
        foreach ($classroomsBySchoolYear as $schoolYearId => $classrooms) {
            // Find the corresponding subjects for this specific school year
            $subjects = $subjectsBySchoolYear->get($schoolYearId);

            // Skip if there are no subjects for this school year
            if (is_null($subjects) || $subjects->isEmpty()) {
                continue;
            }

            // 3. For each classroom in this group, assign all subjects from the same group
            foreach ($classrooms as $classroom) {
                foreach ($subjects as $subject) {
                    ClassSubject::firstOrCreate([
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                    ]);
                }
            }
        }

        $this->command->info('Assigned all subjects to classrooms within the correct school year.');
    }
}
