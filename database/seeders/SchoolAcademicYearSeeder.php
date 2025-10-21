<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use Illuminate\Database\Seeder;

class SchoolAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a single centralized school
        $school = School::factory()->create([
            'name' => 'SDIT Ummatan Wahidah',
            'npsn' => '12345678',
            'address' => 'Jl. Pendidikan No. 123, Jakarta Selatan',
            'postal_code' => '12345',
            'website' => 'www.ummatanwahidah.sch.id',
            'email' => 'info@ummatanwahidah.sch.id',
            'place_date_raport' => 'Jakarta',
            'place_date_sts' => 'Jakarta',
        ]);

        $this->command->info('Created centralized school: ' . $school->name);

        // Create multiple academic years for the school
        $academicYears = [
            ['name' => '2021/2022', 'start' => '2021-07-01', 'end' => '2022-06-30'],
            ['name' => '2022/2023', 'start' => '2022-07-01', 'end' => '2023-06-30'],
            ['name' => '2023/2024', 'start' => '2023-07-01', 'end' => '2024-06-30'],
            ['name' => '2024/2025', 'start' => '2024-07-01', 'end' => '2025-06-30'],
            ['name' => '2025/2026', 'start' => '2025-07-01', 'end' => '2026-06-30'],
        ];

        $createdAcademicYears = [];
        foreach ($academicYears as $yearData) {
            $academicYear = AcademicYear::create($yearData);
            $createdAcademicYears[] = $academicYear;

            // Create the pivot relationship between school and academic year
            SchoolAcademicYear::create([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
            ]);

            $this->command->info("Created academic year: {$academicYear->name} and linked to school");
        }

        // Set the most recent academic year as current
        $school->update([
            'current_academic_year_id' => end($createdAcademicYears)->id,
        ]);

        $this->command->info("Set current academic year to: " . end($createdAcademicYears)->name);
        $this->command->info('School seeder completed successfully!');
    }
}
