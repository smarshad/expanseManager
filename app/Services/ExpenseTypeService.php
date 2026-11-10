<?php

namespace App\Services;

use App\DTOs\ExpenseForDTO;
use App\Interfaces\ExpenseTypeRepositoryInterface;
use App\Models\ExpenseFor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\CannotDeleteExpenseTypeException;
use App\Models\User;
use DomainException;

class ExpenseTypeService
{
    // Define a constant for the cache key
    protected const CACHE_KEY_ALL = 'expense_fors.all';

    public function __construct(protected ExpenseTypeRepositoryInterface $expenseTypeRepository) {}

    /**
     * Retrieves paginated expense types.
     */
    public function getExpenseTypePaginated(User $user, ?int $perPage = 15, ?string $search= NULL): LengthAwarePaginator
    {
        $userId = null; // Default: view all

        // Check for 'view all' permission
        if ($user->can('expensetype.view.all')) {
            $userId = null; // No filtering needed
        } elseif ($user->can('expensetype.view.own')) {
            // If only 'view own' is granted, filter by the user's ID
            $userId = $user->id;
        }
        // The controller's authorize check should handle the case where neither is true.

        // Business logic: e.g., apply global filters based on user context
        // Explicitly passing ['*'] for columns to prevent the reported TypeError,
        // even though it has a default value in the repository.
        // Caching is typically not used for paginated results unless the query is very expensive and static.
        return $this->expenseTypeRepository->getPaginated($perPage, ['*'], $userId, $search);
    }

    /**
     * Retrieves a single expense type by ID.
     */
    public function getExpenseTypeById(int $id): ExpenseFor
    {
        // Use the repository's findById method, which should handle the "not found" case (e.g., throwing ModelNotFoundException)
        return $this->expenseTypeRepository->findById($id);
    }

    /**
     * Creates a new expense type.
     */
    public function create(ExpenseForDTO $expenseForDTO): ExpenseFor
    {
        return DB::transaction(function () use ($expenseForDTO) {
            $expenseType = $this->expenseTypeRepository->create($expenseForDTO->toArray());

            // Invalidate the cache for the 'all' list
            Cache::forget(self::CACHE_KEY_ALL);

            return $expenseType;
        });
    }

    /**
     * Updates an existing expense type.
     */
    public function update(int $id, ExpenseForDTO $expenseForDTO): ExpenseFor
    {
        return DB::transaction(function () use ($id, $expenseForDTO) {
            // 1. Core persistence
            $updatedExpenseType = $this->expenseTypeRepository->update($id, $expenseForDTO->toArray());

            // 2. Invalidate the cache for the 'all' list
            Cache::forget(self::CACHE_KEY_ALL);

            return $updatedExpenseType;
        });
    }

    /**
     * Deletes an expense type after validation.
     * @throws CannotDeleteExpenseTypeException
     */
    public function delete(int $id): bool
    {
        // Business logic validation
        $this->validateExpenseTypeCanBeDeleted($id);

        // Perform deletion
        $deleted = $this->expenseTypeRepository->delete($id);

        if (!$deleted) {
            // Throw a more specific exception if the repository failed to delete for an unknown reason
            throw new DomainException('The repository failed to delete the expense type.');
        }

        // Invalidate the cache for the 'all' list
        Cache::forget(self::CACHE_KEY_ALL);

        return true;
    }

    /**
     * Business rule: Check if the expense type can be deleted.
     * @throws DomainException
     */
    private function validateExpenseTypeCanBeDeleted(int $id): void
    {
        if ($this->expenseTypeRepository->hasRelatedExpenses($id)) {
            // Use DomainException with a specific message for the controller to handle
            throw new DomainException('Cannot delete expense type. Related expense records exist.');
        }
    }
}
