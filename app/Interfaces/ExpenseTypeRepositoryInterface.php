<?php

namespace App\Interfaces;

use App\Models\ExpenseFor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExpenseTypeRepositoryInterface
{
    /**
     * Retrieve all records.
     */
    public function getAll(): Collection;

    /**
     * Retrieve records with pagination.
     */
    public function getPaginated(int $perPage = 15, array $columns = ['*'], ?int $userId = null, ?string $search = NULL): LengthAwarePaginator;

    /**
     * Find a record by its ID.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $categoryId): ExpenseFor;

    /**
     * Create a new record.
     */
    public function create(array $details): ExpenseFor;

    /**
     * Update an existing record.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $categoryId, array $newDetails): ExpenseFor;

    /**
     * Delete a record by its ID.
     */
    public function delete(int $categoryId): bool;

    /**
     * Check if there are any related expense records for a given expense type ID.
     */
    public function hasRelatedExpenses(int $expenseTypeId): bool;
}