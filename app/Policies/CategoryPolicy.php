<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('category.view.all')
            || $user->can('category.view.own');
    }

    public function create(User $user): Response
    {
        // Example logic: only 'admin' or 'editor' can create
        if ($user->can('category.create')) {
            return Response::allow();
        }
        return Response::deny('You must be an Administrator or Editor to create a category.');
    }

    public function delete(User $user, Category $category): Response
    {
        // 1. Check for the global 'delete.all' permission.
        if ($user->can('category.delete.all')) {
            return Response::allow();
        }

        // 2. If not, check for the 'delete.own' permission AND verify ownership.
        if ($user->can('category.delete.own')) {
            if ($user->id === $category->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only delete categories you created.');
        }

        // 3. If neither condition is met, deny access.
        return Response::deny('You do not have permission to delete this category.');
    }

    /**
     * Determine whether the user can update the model.
     * THIS IS THE KEY LOGIC FOR EDIT.
     */
    public function update(User $user, Category $category): Response
    {
        // 1. Global permission
        if ($user->can('category.edit.all')) {
            return Response::allow();
        }

        // 2. Own permission + ownership check
        if ($user->can('category.edit.own')) {
            if ($user->id === $category->created_by) {
                return Response::allow();
            }

            return Response::deny('You can only edit categories you created.');
        }

        // 3. No permission at all
        return Response::deny('You do not have permission to update this category.');
    }
}
