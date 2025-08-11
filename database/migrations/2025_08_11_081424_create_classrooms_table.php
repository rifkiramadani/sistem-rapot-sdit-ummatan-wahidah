<?php

use App\Models\AcademicYear;
use App\Models\SchoolAcademicYear;
use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');

            // Foreign key for the Teacher model (the homeroom teacher)
            $table->foreignIdFor(Teacher::class)->constrained()->onDelete('cascade');

            // Foreign key for the SchoolAcademicYear model
            $table->foreignIdFor(SchoolAcademicYear::class)->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
