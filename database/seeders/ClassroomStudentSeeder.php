<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ClassroomStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kelompokkan siswa dan kelas berdasarkan school_academic_year_id mereka
        $studentsBySchoolYear = Student::all()->groupBy('school_academic_year_id');
        $classroomsBySchoolYear = Classroom::all()->groupBy('school_academic_year_id');

        if ($studentsBySchoolYear->isEmpty() || $classroomsBySchoolYear->isEmpty()) {
            $this->command->warn('Tidak ada siswa atau kelas yang ditemukan untuk dimasukkan.');
            return;
        }

        // 2. Iterasi setiap grup tahun ajaran
        foreach ($studentsBySchoolYear as $schoolYearId => $students) {
            // Temukan kelas yang sesuai untuk tahun ajaran ini
            $classrooms = $classroomsBySchoolYear->get($schoolYearId);

            // Lewati jika tidak ada kelas untuk tahun ajaran ini
            if (is_null($classrooms) || $classrooms->isEmpty()) {
                continue;
            }

            // 3. Distribusikan siswa dari tahun ajaran ini ke kelas-kelas di tahun ajaran yang sama
            $classroomIndex = 0;
            foreach ($students as $student) {
                $classroom = $classrooms[$classroomIndex];

                // Gunakan firstOrCreate untuk menghindari duplikat jika seeder dijalankan lagi
                ClassroomStudent::firstOrCreate([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                ]);

                // Pindah ke kelas berikutnya dalam grup ini (round-robin)
                $classroomIndex = ($classroomIndex + 1) % $classrooms->count();
            }
        }

        $this->command->info('Berhasil memasukkan semua siswa ke kelas sesuai tahun ajaran.');
    }
}
