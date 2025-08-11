<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignIdFor(Student::class)->unique()->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('job')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};