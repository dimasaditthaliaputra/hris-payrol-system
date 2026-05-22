<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Concrete User Repository.
 * Implementasi dari UserRepositoryInterface menggunakan Eloquent.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function getByRole(string $roleName): Collection
    {
        return User::whereHas('role', fn ($q) => $q->where('name', $roleName))
            ->where('is_active', true)
            ->get();
    }
}
