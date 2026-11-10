<?php

namespace App\Repositories;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Interfaces\PaymentMethodRepositoryInterface;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{

    public function __construct(protected PaymentMethod $model) {}

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getPaginated(?int $perPage = null, array $columns = ['*'], ?int $userId = null, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->model->with('creator');
        if ($userId !== null) {
            $query->where('created_by', $userId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate(
            $perPage ?? config('pagination.default'),
            $columns
        );
    }

    public function create(array $details): PaymentMethod
    {
        return $this->model->create($details);
    }

    public function update(int $id, array $data): PaymentMethod
    {
        $paymentMethod = $this->findById($id);
        $paymentMethod->update($data);
        return $paymentMethod;
    }

    private function findById(int $id): PaymentMethod
    {
        // Use findOrFail to automatically throw ModelNotFoundException if not found
        return $this->model->findOrFail($id);
    }

    public function hasRelatedExpenses(int $paymentMethodId): bool
    {
        // This assumes you have an 'expenses' relationship defined on the ExpenseFor model
        // and a corresponding 'Expense' model.
        // The check should be fast and efficient.

        // Placeholder for actual implementation:
        // return $this->model->whereHas('expenses', function ($query) use ($paymentMethodId) {
        //     $query->where('expense_for_id', $paymentMethodId);
        // })->exists();

        // For demonstration, we'll assume a direct check on a hypothetical 'Expense' model
        // that has a foreign key 'expense_for_id'.

        // To make this runnable, we'll use a simplified check that assumes the existence of a related model.
        // In a real application, you would need to import the Expense model.

        // return \App\Models\Expense::where('expense_for_id', $paymentMethodId)->exists();

        // Since we don't have the Expense model, we'll return false for now to allow the service to work
        // but the user should uncomment the real logic.
        return false;
    }

    public function delete(int $id): bool
    {
        // Eloquent delete returns a boolean indicating success
        return $this->model->destroy($id);
    }
}
