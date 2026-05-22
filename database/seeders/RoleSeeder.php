<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles table dengan 4 role default sistem HRIS.
     */
    public function run(): void
    {
        $roles = [
            [
                'name'         => Role::SUPER_ADMIN,
                'display_name' => 'Super Admin',
                'description'  => 'Akses penuh ke seluruh sistem. Dapat mengelola role, setting global, dan unlock payroll.',
            ],
            [
                'name'         => Role::HRD,
                'display_name' => 'HRD',
                'description'  => 'Mengelola data karyawan, import absensi, dan generate payroll/slip gaji.',
            ],
            [
                'name'         => Role::FINANCE,
                'display_name' => 'Finance',
                'description'  => 'Melihat dan mengekspor laporan keuangan, serta menyetujui payroll.',
            ],
            [
                'name'         => Role::KARYAWAN,
                'display_name' => 'Karyawan',
                'description'  => 'Melihat slip gaji, riwayat payroll, dan mengunduh PDF slip gaji.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('✅ Roles seeded: Super Admin, HRD, Finance, Karyawan');
    }
}
