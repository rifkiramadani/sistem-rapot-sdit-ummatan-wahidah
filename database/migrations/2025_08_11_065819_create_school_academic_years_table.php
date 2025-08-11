<?php

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_academic_years', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Foreign key for the School model
            $table->foreignIdFor(School::class)->constrained()->onDelete('cascade');

            // Foreign key for the AcademicYear model
            $table->foreignIdFor(AcademicYear::class)->constrained()->onDelete('cascade');

            // Prevent duplicate school-year pairs
            $table->unique(['school_id', 'academic_year_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_academic_years');
    }
};
