<?php

namespace Database\Factories;

use App\Models\SchoolAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        // Create a user with the 'teacher' role first
        $user = User::factory()->asTeacher()->create();

        return [
            // Use the name from the created user
            'name' => $user->name,
            'niy' => $this->faker->unique()->numerify('##########'), // 10-digit NIY

            // Assign the created user's ID
            'user_id' => $user->id,

            // Assign a random school academic year
            'school_academic_year_id' => SchoolAcademicYear::inRandomOrder()->first()->id,
        ];
    }
}
