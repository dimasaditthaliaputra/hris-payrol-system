<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function existsForEmployeeAndDate(int $employeeId, string $date): bool
    {
        return $this->model->where('employee_id', $employeeId)->where('date', $date)->exists();
    }
}
