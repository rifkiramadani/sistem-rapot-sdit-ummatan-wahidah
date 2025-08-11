<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Use company name for a realistic-sounding school name
            'name' => $this->faker->unique()->company(),

            // Generate a random 8-digit NPSN number as a string
            'npsn' => $this->faker->numerify('########'),

            'address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
            'website' => $this->faker->unique()->domainName(),
            'email' => $this->faker->unique()->safeEmail(),

            'school_principal_id' => User::factory()->asPrincipal(),

            'current_academic_year_id' => AcademicYear::inRandomOrder()->first()?->id,
            'place_date_raport' => $this->faker->city(),
            'place_date_sts' => $this->faker->city(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (School $school) {
            // 1. Get the principal user that was just created.
            $principal = $school->principal;

            // 2. Find an academic year to link the school and principal to.
            //    If none exist, create one.
            $academicYear = AcademicYear::inRandomOrder()->first();

            // 3. Create the link in the pivot table.
            $schoolAcademicYear = SchoolAcademicYear::create([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
            ]);

            // 4. Create the teacher record for the principal.
            //    We use Teacher::create() directly to avoid the TeacherFactory's
            //    default behavior of creating another new user.
            Teacher::create([
                'name' => $principal->name,
                'niy' => $this->faker->unique()->numerify('##########'),
                'user_id' => $principal->id,
                'school_academic_year_id' => $schoolAcademicYear->id,
            ]);
        });
    }
}
