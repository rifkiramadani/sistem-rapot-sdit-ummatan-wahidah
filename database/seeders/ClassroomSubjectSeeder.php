<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ClassroomSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kelompokkan kelas dan mapel berdasarkan tahun ajaran
        $classroomsBySchoolYear = Classroom::all()->groupBy('school_academic_year_id');
        $subjectsBySchoolYear = Subject::all()->groupBy('school_academic_year_id');

        if ($classroomsBySchoolYear->isEmpty() || $subjectsBySchoolYear->isEmpty()) {
            $this->command->warn('Tidak ada kelas atau mata pelajaran untuk ditautkan.');
            return;
        }

        // 2. Iterasi setiap grup kelas
        foreach ($classroomsBySchoolYear as $schoolYearId => $classrooms) {
            // Temukan mapel yang sesuai untuk tahun ajaran ini
            $subjects = $subjectsBySchoolYear->get($schoolYearId);

            if (is_null($subjects) || $subjects->isEmpty()) {
                continue;
            }

            // 3. Untuk setiap kelas, tetapkan semua mapel dari tahun ajaran yang sama
            foreach ($classrooms as $classroom) {
                foreach ($subjects as $subject) {
                    ClassroomSubject::firstOrCreate([
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                    ]);
                }
            }
        }

        $this->command->info('Berhasil menautkan mata pelajaran ke kelas sesuai tahun ajaran.');
    }
}
