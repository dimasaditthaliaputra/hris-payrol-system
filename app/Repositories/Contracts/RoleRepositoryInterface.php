<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Role Repository Interface.
 * Kontrak untuk semua operasi database yang berkaitan dengan Role.
 */
interface RoleRepositoryInterface extends RepositoryInterface
{
    /**
     * Temukan role berdasarkan nama slug (e.g. 'super_admin').
     */
    public function findByName(string $name): ?\App\Models\Role;
}
