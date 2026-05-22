<?php

namespace App\Repositories\Contracts;

interface IncentiveRepositoryInterface extends RepositoryInterface
{
    public function sumIncentivesByEmployeeAndMonth($employeeId, $month, $year);
}
