<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;

/**
 * Concrete Role Repository.
 * Implementasi dari RoleRepositoryInterface menggunakan Eloquent.
 */
class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }
}
