<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SchoolAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a single centralized school directly without using factory's configure method
        $school = School::create([
            'name' => 'SDIT Ummatan Wahidah',
            'npsn' => '12345678',
            'address' => 'Jl. Pendidikan No. 123, Jakarta Selatan',

            // --- TAMBAHKAN INI ---
            'village' => 'Pondok Indah',
            'district' => 'Kebayoran Lama',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            // --- BATAS TAMBAHAN ---

            'postal_code' => '12345',
            'website' => 'www.ummatanwahidah.sch.id',
            'email' => 'info@ummatanwahidah.sch.id',
            'place_date_raport' => 'Jakarta',
            'place_date_sts' => 'Jakarta',
        ]);

        $this->command->info("Created centralized school: {$school->name}");

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

        // Create a principal user and assign to the school
        $principalRole = Role::where('name', RoleEnum::PRINCIPAL->value)->first();

        if (!$principalRole) {
            $this->command->error('Principal role not found! Please run RoleSeeder first.');
            return;
        }

        $principalUser = User::create([
            'name' => 'Ahmad Wijaya, S.Pd.',
            'email' => 'kepsek@ummatanwahidah.sch.id',
            'password' => Hash::make('password'),
            'role_id' => $principalRole->id,
        ]);

        // Assign principal to school
        $school->update([
            'school_principal_id' => $principalUser->id,
        ]);

        // Create teacher record for the principal in the most recent academic year
        $latestSchoolAcademicYear = SchoolAcademicYear::where('school_id', $school->id)
            ->where('academic_year_id', end($createdAcademicYears)->id)
            ->first();

        Teacher::create([
            'name' => $principalUser->name,
            'niy' => '2021001',
            'user_id' => $principalUser->id,
            'school_academic_year_id' => $latestSchoolAcademicYear->id,
        ]);

        $this->command->info("Created principal user: {$principalUser->name} (kepsek@ummatanwahidah.sch.id / password)");
        $this->command->info('School seeder completed successfully!');
    }
}
