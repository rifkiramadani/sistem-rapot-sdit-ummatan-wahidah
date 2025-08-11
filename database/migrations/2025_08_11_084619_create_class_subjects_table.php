<?php

use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Foreign key for the Classroom model
            $table->foreignIdFor(Classroom::class)->constrained()->onDelete('cascade');

            // Foreign key for the Subject model
            $table->foreignIdFor(Subject::class)->constrained()->onDelete('cascade');

            // Optional: Prevent a class from having the same subject twice
            $table->unique(['classroom_id', 'subject_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subjects');
    }
};
