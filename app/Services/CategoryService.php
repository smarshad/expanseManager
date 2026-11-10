<?php


namespace App\Services;

use App\DTOs\CategoryDTO;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getCategoriesPaginated(User $user, ?int $perPage = 15,  ?string $search = null): LengthAwarePaginator
    {
        $userId = null; // Default: view all

        // Check for 'view all' permission
        if ($user->can('category.view.all')) {
            $userId = null; // No filtering needed
        } elseif ($user->can('category.view.own')) {
            // If only 'view own' is granted, filter by the user's ID
            $userId = $user->id;
        }
        // The controller's authorize check should handle the case where neither is true.

        // Business logic: e.g., apply global filters based on user context
        // Explicitly passing ['*'] for columns to prevent the reported TypeError,
        // even though it has a default value in the repository.
        return $this->categoryRepository->getPaginated($perPage, ['*'], $userId, $search);
    }

    public function getCategoryById(int $categoryId): Category
    {
        return $this->categoryRepository->findById($categoryId);
    }

    /**
     * Creates a new category within a database transaction.
     */
    public function createCategory(CategoryDTO $categoryDTO): Category
    {
        // Business logic: e.g., check for profanity, trigger external API call

        return DB::transaction(function () use ($categoryDTO) {
            // 1. Core persistence
            $category = $this->categoryRepository->create($categoryDTO->toArray());

            // 2. Secondary operation (e.g., logging, creating a related resource)
            // Example: Log the creation event to an audit table
            // AuditLog::create(['user_id' => auth()->id(), 'action' => 'Category Created', 'category_id' => $category->id]);

            return $category;
        });
    }

    /**
     * Updates an existing category within a database transaction.
     */
    public function updateCategory(int $categoryId, CategoryDTO $categoryDTO): Category
    {
        return DB::transaction(function () use ($categoryId, $categoryDTO) {
            // 1. Core persistence
            $updatedCategory = $this->categoryRepository->update($categoryId, $categoryDTO->toArray());

            // 2. Secondary operation (e.g., cache invalidation)
            // Cache::forget('all_categories');

            return $updatedCategory;
        });
    }

    public function deleteCategory(int $categoryId): bool
    {
        // Business logic: e.g., check if category is in use before deleting

        // 1. Check if category is used in the expenses table
        // $expenseExists = Expense::where('category_id', $categoryId)->exists();

        // if ($expenseExists) {
        //     // Category cannot be deleted
        //     return false;
        // }

        // 2. Otherwise delete normally using repository
        return $this->categoryRepository->delete($categoryId);
    }
}
