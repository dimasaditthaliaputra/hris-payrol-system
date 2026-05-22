<?php

namespace App\Repositories;

use App\Models\Incentive;
use App\Repositories\Contracts\IncentiveRepositoryInterface;

class IncentiveRepository extends BaseRepository implements IncentiveRepositoryInterface
{
    public function __construct(Incentive $model)
    {
        parent::__construct($model);
    }

    public function sumIncentivesByEmployeeAndMonth($employeeId, $month, $year)
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }
}
