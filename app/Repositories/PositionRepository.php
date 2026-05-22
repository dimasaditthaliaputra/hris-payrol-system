<?php

namespace App\Repositories;

use App\Models\Position;
use App\Repositories\Contracts\PositionRepositoryInterface;

class PositionRepository extends BaseRepository implements PositionRepositoryInterface
{
    public function __construct(Position $model)
    {
        parent::__construct($model);
    }

    public function all(array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with('department')->get($columns);
    }
}
