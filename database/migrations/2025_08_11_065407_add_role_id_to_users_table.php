<?php

use App\Models\Role; // Import the Role model
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
        Schema::table('users', function (Blueprint $table) {
            // pakai char(26) agar sesuai dengan ULID di tabel roles
            $table->char('role_id', 26)->nullable()->after('id');

            // tambahkan constraint foreign key
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // This is the proper way to drop a foreign key in Laravel
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
