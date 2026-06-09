<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']);
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    public function find(int $id, array $columns = ['*']): ?Model;
    public function findByUuid(string $uuid): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
