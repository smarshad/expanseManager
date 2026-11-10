<?php

namespace App\Interfaces;

use App\Models\Expense;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ExpenseRepositoryInterface
{
    /**
     * Retrieve all records
     */

    public function getAll(): Collection;

    /**
     * Retrieve records with pagination
     */

    public function getPaginated(int $perPage = 15, array $columns = ['*'], ?int $userId = null, ?string $search = NULL, array $filters = []): LengthAwarePaginator;

    /**
     * Create new Record
     */

    public function create(array $data): Expense;

    /**
     * Update an existing Record
     */

    public function update(int $id, array $data): Expense;


    /**
     * Delete a record by its ID.
     */
    public function delete(int $id): bool;
}
