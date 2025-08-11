<?php

use App\Models\AcademicYear;
use App\Models\User;
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
        Schema::create('schools', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name')->unique();
            $table->string('npsn')->nullable();

            $table->text('address');
            $table->string('postal_code', 10)->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();

            $table->foreignIdFor(User::class, 'school_principal_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->foreignIdFor(AcademicYear::class, 'current_academic_year_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->string('place_date_raport')->nullable();
            $table->string('place_date_sts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
