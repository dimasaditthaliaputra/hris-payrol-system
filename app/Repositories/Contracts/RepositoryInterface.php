<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Base Repository Interface.
 * Semua Repository Interface akan meng-extend interface ini.
 * Ini adalah kontrak dasar yang memastikan semua repository memiliki operasi CRUD standar.
 */
interface RepositoryInterface
{
    public function all(array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;
    public function find(int $id, array $columns = ['*']): ?\Illuminate\Database\Eloquent\Model;
    public function findOrFail(int $id): \Illuminate\Database\Eloquent\Model;
    public function create(array $data): \Illuminate\Database\Eloquent\Model;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
    public function model(): \Illuminate\Database\Eloquent\Model;
}
