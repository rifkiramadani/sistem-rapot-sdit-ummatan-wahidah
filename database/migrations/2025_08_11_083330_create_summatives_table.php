<?php

use App\Models\Subject;
use App\Models\SummativeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summatives', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('identifier')->nullable();

            // Foreign key untuk tabel subjects
            $table->foreignIdFor(Subject::class)->constrained()->onDelete('cascade');

            // Foreign key untuk tabel summative_types
            $table->foreignIdFor(SummativeType::class)->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summatives');
    }
};
