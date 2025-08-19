<?php

use App\Models\Classroom;
use App\Models\Subject;
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
        Schema::create('classroom_subjects', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Foreign key untuk model Classroom
            $table->foreignIdFor(Classroom::class)->constrained()->onDelete('cascade');

            // Foreign key untuk model Subject
            $table->foreignIdFor(Subject::class)->constrained()->onDelete('cascade');

            // Mencegah satu kelas memiliki mata pelajaran yang sama lebih dari sekali
            $table->unique(['classroom_id', 'subject_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_subjects');
    }
};
