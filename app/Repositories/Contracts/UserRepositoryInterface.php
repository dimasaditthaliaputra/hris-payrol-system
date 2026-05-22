<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * User Repository Interface.
 * Kontrak untuk semua operasi database yang berkaitan dengan User.
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Temukan user berdasarkan email.
     */
    public function findByEmail(string $email): ?\App\Models\User;

    /**
     * Ambil semua user aktif berdasarkan role.
     */
    public function getByRole(string $roleName): \Illuminate\Database\Eloquent\Collection;
}
