<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\StudentParent;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nisn' => $this->faker->unique()->numerify('##########'), // 10-digit NISN
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(GenderEnum::class),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-18 years', '-6 years'),
            'religion' => $this->faker->randomElement(ReligionEnum::class),
            'last_education' => $this->faker->randomElement(['SMP', 'MTS', null]),
            'address' => $this->faker->address(),
            'school_academic_year_id' => SchoolAcademicYear::inRandomOrder()->first()->id,
        ];
    }

    /**
     * Configure the model factory.
     * This is where we create the related models.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Student $student) {
            // Create a parent record for the student
            StudentParent::factory()->create([
                'student_id' => $student->id,
            ]);

            // Create a guardian record for the student
            StudentGuardian::factory()->create([
                'student_id' => $student->id,
            ]);
        });
    }
}
