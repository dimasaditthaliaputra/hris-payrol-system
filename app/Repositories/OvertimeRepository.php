<?php

namespace App\Repositories;

use App\Models\Overtime;
use App\Repositories\Contracts\OvertimeRepositoryInterface;

class OvertimeRepository extends BaseRepository implements OvertimeRepositoryInterface
{
    public function __construct(Overtime $model)
    {
        parent::__construct($model);
    }
}
