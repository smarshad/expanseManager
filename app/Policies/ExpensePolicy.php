<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expense.view.all')
            || $user->can('expense.view.own');
    }

    public function create(User $user): Response
    {
        // Example logic: only 'admin' or 'editor' can create
        if ($user->can('expense.create')) {
            return Response::allow();
        }
        return Response::deny('You must be an Administrator or Editor to create a expense.');
    }

    public function delete(User $user, Expense $expense): Response
    {
        // 1. Check for the global 'delete.all' permission.
        if ($user->can('expense.delete.all')) {
            return Response::allow();
        }

        // 2. If not, check for the 'delete.own' permission AND verify ownership.
        if ($user->can('expense.delete.own')) {
            if ($user->id === $expense->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only delete expense you created.');
        }

        // 3. If neither condition is met, deny access.
        return Response::deny('You do not have permission to delete this expense.');
    }

    public function show(User $user, Expense $expense): Response
    {
        // 1. Check for the global 'show' permission.
        if ($user->can('expense.show')) {
            return Response::allow();
        }
        
        // 3. If neither condition is met, deny access.
        return Response::deny('You do not have permission to show this expense.');
    }

    public function update(User $user, Expense $expense): Response
    {
        // 1. Check for the global 'edit.all' permission.
        if ($user->can('expense.edit.all')) {
            return Response::allow();
        }

        // 2. If not, check for the 'edit.own' permission AND verify ownership.
        if ($user->can('expense.edit.own')) {
            if ($user->id === $expense->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only edit categories you created.');
        }

        // 3. If neither condition is met, deny access.
        return Response::deny('You do not have permission to edit this expense.');
    }
}
