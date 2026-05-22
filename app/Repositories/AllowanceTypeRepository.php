<?php

namespace App\Repositories;

use App\Models\AllowanceType;
use App\Repositories\Contracts\AllowanceTypeRepositoryInterface;

class AllowanceTypeRepository extends BaseRepository implements AllowanceTypeRepositoryInterface
{
    public function __construct(AllowanceType $model)
    {
        parent::__construct($model);
    }
}
