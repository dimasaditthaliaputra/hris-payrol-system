<?php

namespace App\Repositories;

use App\Models\DeductionType;
use App\Repositories\Contracts\DeductionTypeRepositoryInterface;

class DeductionTypeRepository extends BaseRepository implements DeductionTypeRepositoryInterface
{
    public function __construct(DeductionType $model)
    {
        parent::__construct($model);
    }
}
