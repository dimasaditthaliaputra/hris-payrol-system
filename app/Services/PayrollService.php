<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\Incentive;
use App\Models\CashAdvanceInstallment;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Repositories\Contracts\PayrollDetailRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class PayrollService
{
    public function __construct(
        protected PayrollRepositoryInterface $payrollRepository,
        protected PayrollDetailRepositoryInterface $payrollDetailRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Cek apakah periode payroll tertentu sudah dikunci.
     * Dipakai oleh Service lain (Attendance, Incentive, dll.)
     */
    public function isPeriodLocked(int $month, int $year): bool
    {
        $payroll = $this->payrollRepository->findByPeriod($month, $year);
        return $payroll && $payroll->isLocked();
    }

    /**
     * Generate payroll batch untuk semua karyawan aktif pada bulan/tahun tertentu.
     *
     * Validasi:
     *  1. Tidak boleh duplikat periode (unique constraint + pengecekan manual).
     *  2. Wajib ada data absensi pada periode tersebut.
     */
    public function generateBatch(int $month, int $year): array
    {
        // Validasi 1: Cek duplikat periode
        $existingPayroll = $this->payrollRepository->findByPeriod($month, $year);
        if ($existingPayroll) {
            throw new Exception("Payroll untuk periode {$month}/{$year} sudah pernah digenerate (ID: {$existingPayroll->id}). Hapus/buka kunci terlebih dahulu.");
        }

        // Validasi 2: Wajib ada data absensi
        $attendanceExists = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->exists();
        if (!$attendanceExists) {
            throw new Exception("Tidak ditemukan data absensi untuk periode {$month}/{$year}. Payroll tidak dapat digenerate.");
        }

        try {
            DB::beginTransaction();

            // Buat header payroll
            $payroll = $this->payrollRepository->create([
                'month'          => $month,
                'year'           => $year,
                'status'         => 'draft',
                'generated_by'   => auth()->id(),
                'total_expenditure' => 0,
                'employee_count' => 0,
            ]);

            // Ambil semua karyawan aktif beserta relasi yang dibutuhkan
            $employees = Employee::whereNull('deleted_at')
                ->with([
                    'allowances',
                    'deductions',
                    'position',
                ])
                ->get();

            $totalExpenditure = 0;
            $employeeCount    = 0;
            $details          = [];

            foreach ($employees as $employee) {
                $detail = $this->calculateEmployeeSalary($employee, $payroll->id, $month, $year);

                // Simpan detail ke database
                $this->payrollDetailRepository->create(array_merge($detail, [
                    'payroll_id'  => $payroll->id,
                    'employee_id' => $employee->id,
                ]));

                $totalExpenditure += $detail['net_salary'];
                $employeeCount++;
                $details[] = $detail;
            }

            // Update total di header payroll
            $this->payrollRepository->update($payroll->id, [
                'total_expenditure' => $totalExpenditure,
                'employee_count'    => $employeeCount,
            ]);

            $this->activityLogService->log(
                'generate_payroll',
                "Generate payroll periode {$month}/{$year}. Total {$employeeCount} karyawan, total pengeluaran: " . number_format($totalExpenditure, 0, ',', '.')
            );

            DB::commit();

            return [
                'payroll_id'        => $payroll->id,
                'employee_count'    => $employeeCount,
                'total_expenditure' => $totalExpenditure,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Menghitung komponen gaji satu karyawan untuk periode tertentu.
     * Ini adalah inti formula: Gaji Bersih = Pokok + Tunjangan + Lembur + Insentif - Potongan - BPJS - PPh21 - Cicilan
     */
    private function calculateEmployeeSalary(Employee $employee, int $payrollId, int $month, int $year): array
    {
        $basicSalary = (float) $employee->basic_salary;

        // ── KOMPONEN PENDAPATAN ──────────────────────────────────────────────

        // 1. Total Tunjangan (dari tabel employee_allowances)
        $totalAllowance = (float) $employee->allowances->sum('amount');

        // 2. Uang Lembur (total jam lembur × tarif per jam dari positions.overtime_rate)
        $overtimeRate  = (float) ($employee->position->overtime_rate ?? 0);
        $overtimeHours = (float) Overtime::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('duration_hours');
        $overtimePay = $overtimeHours * $overtimeRate;

        // 3. Insentif bulanan
        $incentive = (float) Incentive::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        // 4. THR (default 0, akan diperhitungkan jika THR digenerate bulan ini)
        $thr = 0;

        // ── KOMPONEN POTONGAN ────────────────────────────────────────────────

        // 5. Total Potongan Manual (dari tabel employee_deductions)
        $totalDeduction = (float) $employee->deductions->sum('amount');

        // 6. BPJS (jika ada data di kolom karyawan)
        $bpjsKesehatan       = (float) ($employee->bpjs_kesehatan ?? 0);
        $bpjsKetenagakerjaan = (float) ($employee->bpjs_ketenagakerjaan ?? 0);

        // 7. PPh 21 — Estimasi sederhana (bisa dikembangkan ke kalkulasi progresif)
        // Dasar PKP = Gaji Pokok + Tunjangan - BPJS per tahun
        $annualTaxableIncome = ($basicSalary + $totalAllowance - $bpjsKesehatan - $bpjsKetenagakerjaan) * 12;
        $pph21Monthly        = $this->calculatePph21Monthly($annualTaxableIncome, $employee->status_pajak);

        // 8. Cicilan kasbon (ambil installment yang jatuh tempo bulan ini, masih unpaid)
        $installment = CashAdvanceInstallment::whereHas('cashAdvance', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)->where('status', 'approved');
            })
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'unpaid')
            ->sum('amount');
        $cashAdvanceInstallment = (float) $installment;

        // ── KALKULASI FINAL ──────────────────────────────────────────────────
        $grossSalary = $basicSalary + $totalAllowance + $overtimePay + $incentive + $thr;
        $totalCuts   = $totalDeduction + $bpjsKesehatan + $bpjsKetenagakerjaan + $pph21Monthly + $cashAdvanceInstallment;
        $netSalary   = max(0, $grossSalary - $totalCuts); // Tidak boleh minus

        // ── DATA PENDUKUNG ───────────────────────────────────────────────────
        $workingDays = (int) Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        return [
            'basic_salary'             => $basicSalary,
            'total_allowance'          => $totalAllowance,
            'overtime_pay'             => $overtimePay,
            'incentive'                => $incentive,
            'thr'                      => $thr,
            'total_deduction'          => $totalDeduction,
            'bpjs_kesehatan'           => $bpjsKesehatan,
            'bpjs_ketenagakerjaan'     => $bpjsKetenagakerjaan,
            'pph21'                    => $pph21Monthly,
            'cash_advance_installment' => $cashAdvanceInstallment,
            'gross_salary'             => $grossSalary,
            'total_cuts'               => $totalCuts,
            'net_salary'               => $netSalary,
            'working_days'             => $workingDays,
            'overtime_hours'           => $overtimeHours,
        ];
    }

    /**
     * Kalkulasi PPh 21 bulanan berdasarkan tarif progresif UU No.7/2021 (UU HPP).
     * Tarif berlaku untuk PTKP status TK/0 (bisa dikembangkan berdasarkan status_pajak).
     */
    private function calculatePph21Monthly(float $annualTaxableIncome, ?string $statusPajak): float
    {
        // PTKP berdasarkan status pajak karyawan
        $ptkp = match ($statusPajak) {
            'TK/0' => 54_000_000,
            'TK/1' => 58_500_000,
            'TK/2' => 63_000_000,
            'TK/3' => 67_500_000,
            'K/0'  => 58_500_000,
            'K/1'  => 63_000_000,
            'K/2'  => 67_500_000,
            'K/3'  => 72_000_000,
            default => 54_000_000, // Default TK/0
        };

        $pkp = max(0, $annualTaxableIncome - $ptkp);

        // Tarif Progresif UU HPP (berlaku sejak 2022)
        $pph21Annual = 0;
        if ($pkp <= 60_000_000) {
            $pph21Annual = $pkp * 0.05;
        } elseif ($pkp <= 250_000_000) {
            $pph21Annual = (60_000_000 * 0.05) + (($pkp - 60_000_000) * 0.15);
        } elseif ($pkp <= 500_000_000) {
            $pph21Annual = (60_000_000 * 0.05) + (190_000_000 * 0.15) + (($pkp - 250_000_000) * 0.25);
        } elseif ($pkp <= 5_000_000_000) {
            $pph21Annual = (60_000_000 * 0.05) + (190_000_000 * 0.15) + (250_000_000 * 0.25) + (($pkp - 500_000_000) * 0.30);
        } else {
            $pph21Annual = (60_000_000 * 0.05) + (190_000_000 * 0.15) + (250_000_000 * 0.25) + (4_500_000_000 * 0.30) + (($pkp - 5_000_000_000) * 0.35);
        }

        return round($pph21Annual / 12, 2); // Dibagi 12 untuk nilai bulanan
    }

    /**
     * Mengunci payroll. Otomatis mengubah status cicilan kasbon terkait menjadi 'paid'.
     */
    public function lockPayroll(int $payrollId): bool
    {
        try {
            DB::beginTransaction();

            $payroll = $this->payrollRepository->findById($payrollId);

            if ($payroll->isLocked()) {
                throw new Exception('Payroll ini sudah dalam status terkunci (locked).');
            }

            // Update status cicilan kasbon yang terkait di bulan ini menjadi 'paid'
            CashAdvanceInstallment::whereHas('cashAdvance', function ($q) {
                    $q->where('status', 'approved');
                })
                ->where('month', $payroll->month)
                ->where('year', $payroll->year)
                ->where('status', 'unpaid')
                ->update(['status' => 'paid']);

            // Cek apakah semua cicilan sudah lunas, update status kasbon jika perlu
            $this->checkAndUpdateCashAdvancePaidOff($payroll->month, $payroll->year);

            // Lock payroll
            $this->payrollRepository->update($payrollId, [
                'status'      => 'locked',
                'locked_at'   => now(),
                // 'approved_by' => auth()->id(), // Jangan timpa approval dari Finance
            ]);

            $this->activityLogService->log(
                'lock_payroll',
                "Mengunci (lock) payroll ID: {$payrollId} untuk periode {$payroll->month}/{$payroll->year}."
            );

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Membuka kunci payroll. HANYA boleh dilakukan oleh Super Admin.
     */
    public function unlockPayroll(int $payrollId): bool
    {
        if (!auth()->user()->isSuperAdmin()) {
            throw new Exception('Hanya Super Admin yang dapat membuka kunci payroll.');
        }

        try {
            DB::beginTransaction();

            $payroll = $this->payrollRepository->findById($payrollId);

            if (!$payroll->isLocked()) {
                throw new Exception('Payroll ini belum dalam status terkunci.');
            }

            // Kembalikan status cicilan kasbon yang sudah dibayar pada periode ini
            CashAdvanceInstallment::whereHas('cashAdvance', function ($q) {
                    $q->where('status', 'approved');
                })
                ->where('month', $payroll->month)
                ->where('year', $payroll->year)
                ->where('status', 'paid')
                ->update(['status' => 'unpaid']);

            $this->payrollRepository->update($payrollId, [
                'status'      => 'draft',
                'locked_at'   => null,
                'approved_by' => null,
            ]);

            $this->activityLogService->log(
                'unlock_payroll',
                "Membuka kunci (unlock) payroll ID: {$payrollId} untuk periode {$payroll->month}/{$payroll->year}."
            );

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Setelah lock, cek apakah semua cicilan sebuah kasbon sudah lunas.
     * Jika ya, ubah status kasbon menjadi 'paid_off'.
     */
    private function checkAndUpdateCashAdvancePaidOff(int $month, int $year): void
    {
        $affectedCashAdvances = CashAdvanceInstallment::where('month', $month)
            ->where('year', $year)
            ->pluck('cash_advance_id')
            ->unique();

        foreach ($affectedCashAdvances as $cashAdvanceId) {
            $hasUnpaid = CashAdvanceInstallment::where('cash_advance_id', $cashAdvanceId)
                ->where('status', 'unpaid')
                ->exists();

            if (!$hasUnpaid) {
                \App\Models\CashAdvance::where('id', $cashAdvanceId)
                    ->where('status', 'approved')
                    ->update(['status' => 'paid_off']);
            }
        }
    }

    /**
     * Menghapus payroll beserta detail-nya (hanya yang berstatus draft).
     */
    public function deletePayroll(int $payrollId): bool
    {
        $payroll = $this->payrollRepository->findById($payrollId);

        if ($payroll->isLocked()) {
            throw new Exception('Payroll yang sudah terkunci tidak dapat dihapus. Buka kunci terlebih dahulu.');
        }

        $payroll->details()->delete();
        $this->payrollRepository->delete($payrollId);

        $this->activityLogService->log(
            'delete_payroll',
            "Menghapus draft payroll ID: {$payrollId} periode {$payroll->month}/{$payroll->year}."
        );

        return true;
    }

    /**
     * Approval oleh Finance.
     */
    public function approvePayroll(int $payrollId): bool
    {
        try {
            $payroll = $this->payrollRepository->findById($payrollId);

            if ($payroll->isLocked()) {
                throw new Exception('Payroll sudah terkunci, tidak dapat mengubah status persetujuan.');
            }

            if ($payroll->approved_by !== null) {
                 throw new Exception('Payroll ini sudah disetujui sebelumnya.');
            }

            $this->payrollRepository->update($payrollId, [
                'approved_by' => auth()->id(),
            ]);

            $this->activityLogService->log(
                'approve_payroll',
                "Menyetujui (approve) draft payroll ID: {$payrollId} periode {$payroll->month}/{$payroll->year}."
            );

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
