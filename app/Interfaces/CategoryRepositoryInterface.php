<?php

namespace App\Interfaces;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function getAll(): Collection;
    public function getPaginated(int $perPage = 15, array $columns = ['*'], ?int $userId = null, ?string $search = NULL): LengthAwarePaginator;
    public function findById(int $categoryId): ?Category;
    public function create(array $details): Category;
    public function update(int $categoryId, array $newDetails): Category;
    public function delete(int $categoryId): bool;
}
