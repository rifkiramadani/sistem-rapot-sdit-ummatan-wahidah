<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SummativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Deskripsi tetap menjadi data dasar dari factory.
            'description' => $this->faker->paragraph(),
        ];
    }

    /**
     * State untuk Sumatif Materi dengan penomoran urut.
     */
    public function asMateri(int $counter): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'M' . $counter,
            'identifier' => 'S' . $counter,
        ]);
    }

    /**
     * State untuk Sumatif Tengah Semester (STS).
     */
    public function asSTS(string $name): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $name,
            'identifier' => null,
        ]);
    }

    /**
     * State untuk Sumatif Akhir Semester (SAS).
     */
    public function asSAS(string $name): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $name,
            'identifier' => null,
        ]);
    }
}
