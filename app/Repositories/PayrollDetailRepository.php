<?php

namespace App\Repositories;

use App\Models\PayrollDetail;
use App\Repositories\Contracts\PayrollDetailRepositoryInterface;

class PayrollDetailRepository extends BaseRepository implements PayrollDetailRepositoryInterface
{
    public function __construct(PayrollDetail $model)
    {
        parent::__construct($model);
    }
}
