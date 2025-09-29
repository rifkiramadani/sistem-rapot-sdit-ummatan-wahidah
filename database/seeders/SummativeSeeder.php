<?php

namespace Database\Seeders;

use App\Enums\DefaultSummativeTypeEnum;
use App\Models\ClassroomSubject; // <-- [UBAH] Import ClassroomSubject
use App\Models\Summative;
use App\Models\SummativeType;
use Illuminate\Database\Seeder;

class SummativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // [UBAH] Ambil semua data ClassroomSubject, bukan Subject
        $classroomSubjects = ClassroomSubject::with('classroom')->get();
        if ($classroomSubjects->isEmpty()) {
            $this->command->warn('Tidak ada data ClassroomSubject. Jalankan ClassroomSubjectSeeder terlebih dahulu.');
            return;
        }

        // [UBAH] Loop untuk setiap hubungan kelas-mapel
        foreach ($classroomSubjects as $classroomSubject) {
            // Counter direset untuk setiap hubungan baru
            $materiCounter = 1;

            // [UBAH] Ambil school_academic_year_id dari kelas yang terkait
            $schoolAcademicYearId = $classroomSubject->classroom->school_academic_year_id;

            // --- Handle Sumatif Materi ---
            $materiType = SummativeType::where('school_academic_year_id', $schoolAcademicYearId)
                ->where('name', DefaultSummativeTypeEnum::MATERI->value)
                ->first();

            if ($materiType) {
                for ($i = 0; $i < 8; $i++) {
                    Summative::factory()
                        ->asMateri($materiCounter++)
                        ->create([
                            // [UBAH] Gunakan classroom_subject_id
                            'classroom_subject_id' => $classroomSubject->id,
                            'summative_type_id' => $materiType->id,
                        ]);
                }
            }

            // --- Handle Sumatif Tengah Semester (STS) ---
            $stsType = SummativeType::where('school_academic_year_id', $schoolAcademicYearId)
                ->where('name', DefaultSummativeTypeEnum::TENGAH_SEMESTER->value)
                ->first();

            if ($stsType) {
                foreach (['NONTES', 'TES', 'NA (STS)'] as $name) {
                    Summative::factory()
                        ->asSTS($name)
                        ->create([
                            // [UBAH] Gunakan classroom_subject_id
                            'classroom_subject_id' => $classroomSubject->id,
                            'summative_type_id' => $stsType->id,
                        ]);
                }
            }

            // --- Handle Sumatif Akhir Semester (SAS) ---
            $sasType = SummativeType::where('school_academic_year_id', $schoolAcademicYearId)
                ->where('name', DefaultSummativeTypeEnum::AKHIR_SEMESTER->value)
                ->first();

            if ($sasType) {
                foreach (['NONTES', 'TES', 'NA (SAS)'] as $name) {
                    Summative::factory()
                        ->asSAS($name)
                        ->create([
                            // [UBAH] Gunakan classroom_subject_id
                            'classroom_subject_id' => $classroomSubject->id,
                            'summative_type_id' => $sasType->id,
                        ]);
                }
            }
        }

        $this->command->info('Berhasil membuat data summatives untuk setiap mata pelajaran di setiap kelas.');
    }
}
