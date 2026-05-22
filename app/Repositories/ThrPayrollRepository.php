<?php

namespace App\Repositories;

use App\Models\ThrPayroll;
use App\Repositories\Contracts\ThrPayrollRepositoryInterface;

class ThrPayrollRepository extends BaseRepository implements ThrPayrollRepositoryInterface
{
    public function __construct(ThrPayroll $model)
    {
        parent::__construct($model);
    }

    public function getThrByPeriod($year)
    {
        return $this->model->with('employee')
            ->where('period_year', $year)
            ->get();
    }

    public function checkExists($employeeId, $year)
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->where('period_year', $year)
            ->exists();
    }
}
