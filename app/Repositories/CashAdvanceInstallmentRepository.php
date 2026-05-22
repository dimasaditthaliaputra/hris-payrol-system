<?php

namespace App\Repositories;

use App\Models\CashAdvanceInstallment;
use App\Repositories\Contracts\CashAdvanceInstallmentRepositoryInterface;

class CashAdvanceInstallmentRepository extends BaseRepository implements CashAdvanceInstallmentRepositoryInterface
{
    public function __construct(CashAdvanceInstallment $model)
    {
        parent::__construct($model);
    }
}
