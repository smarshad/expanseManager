<?php

namespace App\Services;

use App\DTOs\ExpenseDTO;
use App\Repositories\ExpenseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use App\Models\User;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseService
{

    /**
     * Create New Record
     */

    public function __construct(protected ExpenseRepository $expenseRepository) {}

    public function getPaginated(User $user, ?int $perPage = null, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $userId = null; // Default: view all

        // Check for 'view all' permission
        if ($user->can('expense.view.all')) {
            $userId = null; // No filtering needed
        } elseif ($user->can('expense.view.own')) {
            // If only 'view own' is granted, filter by the user's ID
            $userId = $user->id;
        }
        return $this->expenseRepository->getPaginated($perPage, ['*'], $userId, $search, $filters);
    }

    public function create(ExpenseDTO $expenseDTO): Expense
    {
        return DB::transaction(function () use ($expenseDTO) {
            $data = $this->expenseRepository->create($this->withClassification($expenseDTO));
            return $data;
        });
    }

    public function update(int $id, ExpenseDTO $expenseDTO): Expense
    {

        return DB::transaction(function () use ($id, $expenseDTO) {
            $updateData = $this->expenseRepository->update($id, $this->withClassification($expenseDTO));
            return $updateData;
        });
    }

    /**
     * Add spending_nature / recurrence (and transaction_kind when the form left
     * the default) based on the category, so the analysis columns stay in sync.
     */
    private function withClassification(ExpenseDTO $dto): array
    {
        $data = $dto->toArray();
        $category = \App\Models\Category::with('parent.parent')->find($dto->category_id);
        $auto = (new \App\Services\ExpenseClassificationService())->classify($category);

        if ($data['transaction_kind'] === 'expense') {
            $data['transaction_kind'] = $auto['transaction_kind'];
        }

        $nature = match ($dto->nature) {
            'need' => 'essential',
            'want' => 'discretionary',
            default => null,
        };
        $data['spending_nature'] = $data['transaction_kind'] === 'expense'
            ? ($nature ?? $auto['spending_nature'])
            : 'unclassified';

        $data['recurrence'] = $dto->is_recurring && in_array($dto->frequency, ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'], true)
            ? $dto->frequency
            : $auto['recurrence'];

        return $data;
    }

    public function delete(int $id): bool
    {

        $delete = $this->expenseRepository->delete($id);

        if (!$delete) {
            throw new DomainException('The repository failed to delete the payment method.');
        }

        return true;
    }
}
