<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        // Create a unique starting year between 2020 and 2030
        $startYear = $this->faker->unique()->numberBetween(2020, 2030);
        $endYear = $startYear + 1;

        return [
            'name' => "{$startYear}/{$endYear}",
            'start' => "{$startYear}-07-01", // Start of academic year
            'end' => "{$endYear}-06-30",   // End of academic year
        ];
    }
}
