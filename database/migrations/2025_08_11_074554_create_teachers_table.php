<?php

use App\Models\SchoolAcademicYear;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('niy')->unique()->comment('Nomor Induk Yayasan');

            // Foreign key for the User model.
            // It's unique to ensure one teacher profile per user account.
            $table->foreignIdFor(User::class)->unique()->constrained()->onDelete('cascade');

            $table->foreignIdFor(SchoolAcademicYear::class)->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};