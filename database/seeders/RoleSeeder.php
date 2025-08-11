<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RoleEnum::cases() as $roleEnum) {
            // 3. Use firstOrCreate to avoid creating duplicates
            // This will find a role with the given name or create it if not found.
            Role::firstOrCreate(['name' => $roleEnum->value]);
        }
    }
}
