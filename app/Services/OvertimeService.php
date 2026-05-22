<?php

namespace App\Services;

use App\Repositories\Contracts\OvertimeRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class OvertimeService
{
    public function __construct(
        protected OvertimeRepositoryInterface $overtimeRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Placeholder untuk fungsi pengecekan apakah periode terkunci (payroll).
     */
    public function isLocked(string $date): bool
    {
        // TODO: Implementasi logika pengecekan periode payroll yang sudah di-lock.
        return false;
    }
    
    public function calculateAmount(int $employeeId, float $durationHours): float
    {
        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee || !$employee->position) {
            return 0;
        }
        
        return $durationHours * $employee->position->overtime_rate;
    }
}
