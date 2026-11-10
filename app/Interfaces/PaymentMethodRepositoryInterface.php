<?php

namespace App\Interfaces;

use App\Models\PaymentMethod;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentMethodRepositoryInterface
{
    /**
     * Retrieve all records
     */

    public function getAll(): Collection;

    /**
     * Retrieve records with pagination
     */

    public function getPaginated(?int $perPage = null, array $column = ['*'], ?int $userId = null, ?string $search = NULL): LengthAwarePaginator;

    /**
     * Create new Record
     */

    public function create(array $data): PaymentMethod;

    /**
     * Update an existing Record
     */

    public function update(int $id, array $data): PaymentMethod;

    /**
     * Check if there are any related expense records for a given payment method ID.
     */
    public function hasRelatedExpenses(int $id): bool;

    /**
     * Delete a record by its ID.
     */
    public function delete(int $categoryId): bool;
}
