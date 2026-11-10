<?php

namespace App\Policies;

use App\Models\ExpenseFor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpenseForPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expensetype.view.all')
            || $user->can('expensetype.view.own');
    }

    public function create(User $user): Response
    {
        // Example logic: only 'admin' or 'editor' can create
        if ($user->can('expensetype.create')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to create a expense type.');
    }

    public function delete(User $user, ExpenseFor $expensetype): Response
    {
        // 1. Check for the global 'delete.all' permission.
        if ($user->can('expensetype.delete.all')) {
            return Response::allow();
        }

        // 2. If not, check for the 'delete.own' permission AND verify ownership.
        if ($user->can('expensetype.delete.own')) {
            if ($user->id === $expensetype->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only delete categories you created.');
        }

        // 3. If neither condition is met, deny access.
        return Response::deny('You do not have permission to delete this expensetype.');
    }

    /**
     * Determine whether the user can update the model.
     * THIS IS THE KEY LOGIC FOR EDIT.
     */
    public function update(User $user, ExpenseFor $expensetype): Response
    {
        // 1. Global permission
        if ($user->can('expensetype.edit.all')) {
            return Response::allow();
        }
        logAction('editOwn', 'info', ['status'=>$user->can('expensetype.edit.own'), 'userid'=>$user->id, 'created_by'=>$expensetype->created_by]);
        logAction('editAll', 'info', ['status'=>$user->can('expensetype.edit.all'), 'userid'=>$user->id, 'created_by'=>$expensetype->created_by]);
        // 2. Own permission + ownership check
        if ($user->can('expensetype.edit.own')) {
            if ($user->id === $expensetype->created_by) {
                return Response::allow();
            }

            return Response::deny('You can only edit expense type you created.');
        }

        // 3. No permission at all
        return Response::deny('You do not have permission to update this expense type.');
    }
}
