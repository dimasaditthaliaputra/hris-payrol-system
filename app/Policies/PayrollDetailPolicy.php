<?php

namespace App\Policies;

use App\Models\PayrollDetail;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollDetailPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin, HRD, dan Finance selalu bisa download slip siapapun.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin() || $user->isHrd() || $user->isFinance()) {
            return true;
        }

        return null; // Lanjut ke method policy di bawah
    }

    /**
     * Karyawan hanya boleh download slip MILIKNYA sendiri.
     * Validasi ketat berdasarkan user_id (bukan email), mencegah IDOR.
     */
    public function download(User $user, PayrollDetail $payrollDetail): bool
    {
        // Cari employee yang terhubung dengan user ini via user_id (relasi resmi)
        $employee = $user->employee;

        if (!$employee) {
            // User tidak terhubung ke employee manapun — tolak akses
            return false;
        }

        // Pastikan detail payroll ini memang milik karyawan tersebut
        return $payrollDetail->employee_id === $employee->id;
    }
}
