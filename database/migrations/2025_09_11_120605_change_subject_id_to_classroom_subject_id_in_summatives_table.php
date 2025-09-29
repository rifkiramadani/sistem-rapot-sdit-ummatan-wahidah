<?php

use App\Models\ClassroomSubject;
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
        Schema::table('summatives', function (Blueprint $table) {
            // 1. Hapus foreign key dan kolom yang lama
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');

            // 2. Tambahkan foreign key dan kolom yang baru setelah kolom 'identifier'
            $table->foreignIdFor(ClassroomSubject::class)
                ->after('identifier')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * Metode ini akan mengembalikan skema ke keadaan semula jika Anda melakukan rollback.
     */
    public function down(): void
    {
        Schema::table('summatives', function (Blueprint $table) {
            // 1. Hapus foreign key dan kolom yang baru
            $table->dropForeign(['classroom_subject_id']);
            $table->dropColumn('classroom_subject_id');

            // 2. Tambahkan kembali foreign key dan kolom yang lama
            $table->foreignIdFor(Subject::class)->constrained()->onDelete('cascade');
        });
    }
};
