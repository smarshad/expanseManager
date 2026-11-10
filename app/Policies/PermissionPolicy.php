<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{

    public function viewAny(User $user): Response
    {

        if ($user->can('permissions.view')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to see the Permission List.');
    }

    public function create(User $user): Response
    {
        if ($user->can('permissions.create')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to create Permission.');
    }

    public function delete(User $user, Permission $permission): Response
    {
        if ($user->can('permissions.delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to delete this Permission.');
    }

    public function update(User $user, Permission $permission): Response
    {

        if ($user->can('permissions.edit')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this Permission.');
    }
}
