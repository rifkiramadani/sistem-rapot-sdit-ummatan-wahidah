<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_parents', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignIdFor(Student::class)->unique()->constrained()->onDelete('cascade');

            $table->string('father_name');
            $table->string('mother_name');
            $table->string('father_job')->nullable();
            $table->string('mother_job')->nullable();
            $table->text('address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};
