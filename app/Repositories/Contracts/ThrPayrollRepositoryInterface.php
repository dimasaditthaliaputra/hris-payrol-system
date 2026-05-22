<?php

namespace App\Repositories\Contracts;

interface ThrPayrollRepositoryInterface extends RepositoryInterface
{
    public function getThrByPeriod($year);
    public function checkExists($employeeId, $year);
}
