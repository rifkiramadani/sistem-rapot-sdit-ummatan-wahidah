<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::factory()->count(5)->create();

        // [UBAH] Hanya buat SATU data School
        School::factory()->create();
    }
}
