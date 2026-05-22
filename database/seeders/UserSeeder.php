<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed user Super Admin default untuk pertama kali setup sistem.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', Role::SUPER_ADMIN)->first();

        User::firstOrCreate(
            ['email' => 'admin@hris.local'],
            [
                'name'      => 'Super Administrator',
                'password'  => Hash::make('password'),
                'role_id'   => $superAdminRole?->id,
                'phone'     => '081234567890',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Default Super Admin seeded: admin@hris.local / password');
    }
}
