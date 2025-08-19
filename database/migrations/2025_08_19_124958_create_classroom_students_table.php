<?php

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ganti nama tabel dari 'student_classrooms' menjadi 'classroom_students'
        Schema::create('classroom_students', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Foreign key untuk model Student
            $table->foreignIdFor(Student::class)->constrained()->onDelete('cascade');

            // Foreign key untuk model Classroom
            $table->foreignIdFor(Classroom::class)->constrained()->onDelete('cascade');

            // Mencegah seorang siswa dimasukkan ke kelas yang sama lebih dari sekali
            $table->unique(['student_id', 'classroom_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_students');
    }
};
