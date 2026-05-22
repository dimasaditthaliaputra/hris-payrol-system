<?php

namespace App\Repositories\Contracts;

interface PayrollRepositoryInterface extends RepositoryInterface
{
    public function findByPeriod(int $month, int $year): ?\App\Models\Payroll;
}
