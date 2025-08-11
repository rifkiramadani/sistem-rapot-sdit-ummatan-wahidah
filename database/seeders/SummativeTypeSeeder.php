<?php

namespace Database\Seeders;

use App\Models\SchoolAcademicYear;
use App\Models\SummativeType;
use Illuminate\Database\Seeder;

class SummativeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada data SchoolAcademicYear sebelum seeder dijalankan.
        if (SchoolAcademicYear::count() === 0) {
            $this->command->warn('Tidak ada data SchoolAcademicYear. Jalankan SchoolSeeder terlebih dahulu.');
            return;
        }

        // Definisikan jenis-jenis sumatif yang akan dibuat.
        $summativeNames = [
            'Sumatif Materi',
            'Sumatif Tengah Semester',
            'Sumatif Akhir Semester',
        ];

        // Ambil semua data tahun ajaran sekolah.
        $schoolAcademicYears = SchoolAcademicYear::all();

        // Loop untuk setiap tahun ajaran sekolah.
        foreach ($schoolAcademicYears as $schoolAcademicYear) {
            // Loop untuk setiap nama sumatif.
            foreach ($summativeNames as $name) {
                // Gunakan firstOrCreate untuk menghindari duplikat data.
                SummativeType::firstOrCreate(
                    [
                        'school_academic_year_id' => $schoolAcademicYear->id,
                        'name' => $name,
                    ]
                );
            }
        }

        $this->command->info('Berhasil membuat jenis-jenis sumatif untuk semua tahun ajaran sekolah.');
    }
}
