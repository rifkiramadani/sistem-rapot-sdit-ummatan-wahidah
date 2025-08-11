<?php

use App\Models\Student;
use App\Models\Summative;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_summatives', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Foreign key for the Student model
            $table->foreignIdFor(Student::class)->constrained()->onDelete('cascade');

            // Foreign key for the Summative model
            $table->foreignIdFor(Summative::class)->constrained()->onDelete('cascade');

            // Column for the score, restricted to 0-100
            $table->unsignedTinyInteger('value');

            // Optional: Prevent a student from having a score for the same summative twice
            $table->unique(['student_id', 'summative_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_summatives');
    }
};
