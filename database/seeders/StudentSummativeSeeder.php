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

            // Dapatkan ID kelas tempat siswa berada
            $classroomId = $classroomStudent->classroom_id;

            // 2. [UBAH] Dapatkan SEMUA ClassroomSubject ID untuk kelas ini
            $classroomSubjectIds = DB::table('classroom_subjects') // Perhatikan nama tabel di sini
                ->where('classroom_id', $classroomId)
                ->pluck('id'); // Ambil ID dari tabel pivot (ClassroomSubject)

            if ($classroomSubjectIds->isEmpty()) {
                continue; // Skip if the class has no subjects.
            }

            // 3. [UBAH] Cari summatives yang terhubung dengan ClassroomSubject ID yang relevan
            $relevantSummatives = Summative::whereIn('classroom_subject_id', $classroomSubjectIds)
                ->whereHas('summativeType', function ($query) use ($student) {
                // Pastikan SumatifType terkait dengan tahun ajaran sekolah siswa
                $query->where('school_academic_year_id', $student->school_academic_year_id);
                })
                ->get();

            // 4. Create a score record for the student for each relevant summative.
            foreach ($relevantSummatives as $summative) {
                StudentSummative::firstOrCreate([
                    'student_id' => $student->id,
                    'summative_id' => $summative->id,
                ], [
                    'value' => rand(60, 100) // Nilai untuk created, jika belum ada
                ]);
            }
        }

        $this->command->info('Assigned scores to students based on their class subjects.');
    }
}
