<?php

namespace App\Repositories\Contracts;

interface AttendanceRepositoryInterface extends RepositoryInterface
{
    /**
     * Check if attendance exists for employee on specific date
     */
    public function existsForEmployeeAndDate(int $employeeId, string $date): bool;
}
