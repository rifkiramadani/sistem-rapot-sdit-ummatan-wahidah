<?php

namespace Database\Seeders;

use App\Enums\DefaultSummativeTypeEnum;
use App\Models\Subject;
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
        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            $this->command->warn('Tidak ada data Subject. Jalankan SubjectSeeder terlebih dahulu.');
            return;
        }

        // Loop untuk setiap mata pelajaran
        foreach ($subjects as $subject) {
            // Counter direset menjadi 1 untuk setiap mata pelajaran baru
            $materiCounter = 1;

            $schoolAcademicYearId = $subject->school_academic_year_id;

            // --- Handle Sumatif Materi ---
            $materiType = SummativeType::where('school_academic_year_id', $schoolAcademicYearId)
                ->where('name', DefaultSummativeTypeEnum::MATERI->value)
                ->first();

            if ($materiType) {
                for ($i = 0; $i < 8; $i++) {
                    Summative::factory()
                        ->asMateri($materiCounter++) // Gunakan counter lokal
                        ->create([
                            'subject_id' => $subject->id,
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
                            'subject_id' => $subject->id,
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
                            'subject_id' => $subject->id,
                            'summative_type_id' => $sasType->id,
                        ]);
                }
            }
        }

        $this->command->info('Berhasil membuat data summatives untuk setiap mata pelajaran.');
    }
}
