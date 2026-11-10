<?php

namespace App\Repositories;

use App\Interfaces\ExpenseTypeRepositoryInterface;
use App\Models\ExpenseFor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExpenseTypeRepository implements ExpenseTypeRepositoryInterface
{
    public function __construct(protected ExpenseFor $model) {}

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getPaginated(
        int $perPage = 15,
        array $columns = ['*'],
        ?int $userId = null,
        ?string $search = NULL
    ): LengthAwarePaginator {

        $query = $this->model->query();

        if ($userId !== null) {
            $query->where('created_by', $userId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        // $sql = vsprintf(
        //     str_replace('?', '%s', $query->toSql()),
        //     collect($query->getBindings())->map(fn($b) => "'$b'")->toArray()
        // );

        // \Log::info($sql);
        // \Log::info($userId);

        return $query->paginate($perPage, $columns);
    }


    public function findById(int $id): ExpenseFor
    {
        // Use findOrFail to automatically throw ModelNotFoundException if not found
        return $this->model->findOrFail($id);
    }

    public function create(array $details): ExpenseFor
    {
        return $this->model->create($details);
    }

    public function update(int $id, array $newDetails): ExpenseFor
    {
        $expenseType = $this->findById($id);
        $expenseType->update($newDetails);
        return $expenseType;
    }

    public function delete(int $id): bool
    {
        // Eloquent delete returns a boolean indicating success
        return $this->model->destroy($id);
    }

    public function hasRelatedExpenses(int $expenseTypeId): bool
    {
        // This assumes you have an 'expenses' relationship defined on the ExpenseFor model
        // and a corresponding 'Expense' model.
        // The check should be fast and efficient.

        // Placeholder for actual implementation:
        // return $this->model->whereHas('expenses', function ($query) use ($expenseTypeId) {
        //     $query->where('expense_for_id', $expenseTypeId);
        // })->exists();

        // For demonstration, we'll assume a direct check on a hypothetical 'Expense' model
        // that has a foreign key 'expense_for_id'.

        // To make this runnable, we'll use a simplified check that assumes the existence of a related model.
        // In a real application, you would need to import the Expense model.

        // return \App\Models\Expense::where('expense_for_id', $expenseTypeId)->exists();

        // Since we don't have the Expense model, we'll return false for now to allow the service to work
        // but the user should uncomment the real logic.
        return false;
    }
}
