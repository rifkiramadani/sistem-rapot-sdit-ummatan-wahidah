<?php

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use App\Models\SchoolAcademicYear;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nisn')->unique();
            $table->string('name');

            // Using an enum column is efficient for a fixed set of values
            $table->enum('gender', array_column(GenderEnum::cases(), 'value'));

            $table->string('birth_place');
            $table->date('birth_date');

            $table->enum('religion', array_column(ReligionEnum::cases(), 'value'));

            $table->string('last_education')->nullable();
            $table->text('address');

            // Foreign key for the SchoolAcademicYear model
            $table->foreignIdFor(SchoolAcademicYear::class)->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
