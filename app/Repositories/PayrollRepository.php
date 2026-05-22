<?php

namespace App\Repositories;

use App\Models\Payroll;
use App\Repositories\Contracts\PayrollRepositoryInterface;

class PayrollRepository extends BaseRepository implements PayrollRepositoryInterface
{
    public function __construct(Payroll $model)
    {
        parent::__construct($model);
    }

    public function findByPeriod(int $month, int $year): ?Payroll
    {
        return $this->model->where('month', $month)->where('year', $year)->first();
    }
}
