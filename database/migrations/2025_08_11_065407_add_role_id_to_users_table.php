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
            // Add the foreign key column after the 'id' column.
            // It's nullable in case a user might not have a role.
            // It's constrained to the 'roles' table.
            // If a role is deleted, this user's role_id will be set to null.
            $table->foreignIdFor(Role::class)
                ->after('id')
                ->nullable()
                ->constrained()
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
