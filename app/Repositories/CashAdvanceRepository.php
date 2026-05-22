<?php

namespace App\Repositories;

use App\Models\CashAdvance;
use App\Repositories\Contracts\CashAdvanceRepositoryInterface;

class CashAdvanceRepository extends BaseRepository implements CashAdvanceRepositoryInterface
{
    public function __construct(CashAdvance $model)
    {
        parent::__construct($model);
    }
}
