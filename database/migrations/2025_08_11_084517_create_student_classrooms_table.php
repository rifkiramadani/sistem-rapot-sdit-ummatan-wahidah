<?php

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_classrooms', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Foreign key for the Student model
            $table->foreignIdFor(Student::class)->constrained()->onDelete('cascade');

            // Foreign key for the Classroom model
            $table->foreignIdFor(Classroom::class)->constrained()->onDelete('cascade');

            // Optional: Prevent a student from being in the same class twice
            $table->unique(['student_id', 'classroom_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_classrooms');
    }
};
