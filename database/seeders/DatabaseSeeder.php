<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * URUTAN PENTING: roles harus di-seed sebelum users karena ada foreign key.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,  // 1. Seed roles dulu
            UserSeeder::class,  // 2. Baru seed user (butuh role_id)
        ]);
    }
}
