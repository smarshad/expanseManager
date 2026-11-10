<?php

namespace App\Repositories;

use App\Interfaces\ExpenseRepositoryInterface;
use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(protected Expense $model) {}

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): Expense
    {
        return $this->model->create($data);
    }

    public function getPaginated(int $perPage = 15, array $columns = ['*'], ?int $userId = null, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['creator', 'category.parent', 'paymentMethod', 'account', 'paymentPlatform', 'vendor', 'expenseFor']);

        if ($userId !== null) {
            $query->where('created_by', $userId);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $filters['date_to']);
        }
        if (! empty($filters['category_id'])) {
            $categoryIds = \App\Models\Category::query()
                ->where('parent_id', (int) $filters['category_id'])
                ->pluck('id')
                ->push((int) $filters['category_id']);
            $query->whereIn('category_id', $categoryIds);
        }
        foreach (['expense_for_id', 'payment_method_id', 'payment_platform_id', 'account_id', 'vendor_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, (int) $filters[$field]);
            }
        }
        if (! empty($filters['transaction_kind'])) {
            $kind = $filters['transaction_kind'];
            $query->where(function ($kindQuery) use ($kind): void {
                if ($kind === 'investment') {
                    $kindQuery->where('transaction_kind', 'investment')
                        ->orWhere('nature', 'investment')
                        ->orWhereHas('category', fn ($category) => $category
                            ->whereRaw('LOWER(name) = ?', ['investment'])
                            ->orWhereHas('parent', fn ($parent) => $parent->whereRaw('LOWER(name) = ?', ['investment'])));
                } elseif ($kind === 'transfer') {
                    $kindQuery->where('transaction_kind', 'transfer')
                        ->orWhereHas('category', fn ($category) => $category
                            ->whereRaw('LOWER(name) IN (?, ?, ?, ?)', ['cash withdrawal', 'withdrawal', 'credit card bill', 'credit card payment'])
                            ->orWhereHas('parent', fn ($parent) => $parent->whereRaw('LOWER(name) IN (?, ?)', ['transfer', 'finance & transfers'])));
                } elseif ($kind === 'debt') {
                    $kindQuery->whereIn('transaction_kind', ['debt', 'loan'])
                        ->orWhereHas('category', fn ($category) => $category
                            ->whereRaw('LOWER(name) IN (?, ?, ?)', ['borrow return', 'loan repaid', 'debt repayment']));
                } else {
                    $kindQuery->where('transaction_kind', 'expense')
                        ->where(fn ($nature) => $nature->whereNull('nature')->orWhere('nature', '!=', 'investment'))
                        ->whereDoesntHave('category', fn ($category) => $category
                            ->whereRaw('LOWER(name) IN (?, ?, ?, ?, ?, ?, ?, ?)', ['investment', 'cash withdrawal', 'withdrawal', 'credit card bill', 'credit card payment', 'borrow return', 'loan repaid', 'debt repayment'])
                            ->orWhereHas('parent', fn ($parent) => $parent->whereRaw('LOWER(name) IN (?, ?, ?)', ['investment', 'transfer', 'finance & transfers'])));
                }
            });
        }
        if (! empty($filters['nature'])) {
            $query->where('nature', $filters['nature']);
        }
        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($category) use ($search) {
                        $category->where('name', 'like', "%{$search}%")
                            ->orWhereHas('parent', fn ($parent) => $parent->where('name', 'like', "%{$search}%"));
                    })
                    ->orWhereHas('paymentMethod', fn ($method) => $method->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn ($account) => $account->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('paymentPlatform', fn ($platform) => $platform->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vendor', fn ($vendor) => $vendor->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('expenseFor', fn ($expenseFor) => $expenseFor->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->paginate($perPage, $columns);
    }

    public function update(int $id, array $data): Expense
    {
        $expense = $this->findById($id);

        if (array_key_exists('file_path', $data)) {
            $newPath = $data['file_path'];
            $oldPath = $expense->file_path;

            if ($oldPath && $newPath && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $expense->update($data);

        return $expense;
    }

    private function findById(int $id): Expense
    {
        return $this->model->findOrFail($id);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }
}
