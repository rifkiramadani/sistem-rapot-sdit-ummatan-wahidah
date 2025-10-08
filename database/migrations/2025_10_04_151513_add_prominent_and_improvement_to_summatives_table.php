<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('summatives', function (Blueprint $table) {
            // Menambahkan kolom untuk deskripsi materi yang paling menonjol.
            // Tipe TEXT digunakan untuk menyimpan deskripsi yang panjang.
            // Dibuat nullable karena mungkin tidak selalu diisi.
            $table->text('prominent')->nullable()->after('summative_type_id');

            // Menambahkan kolom untuk deskripsi materi yang perlu ditingkatkan.
            $table->text('improvement')->nullable()->after('prominent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('summatives', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback
            $table->dropColumn(['prominent', 'improvement']);
        });
    }
};