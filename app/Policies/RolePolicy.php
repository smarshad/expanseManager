<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function viewAny(User $user): Response
    {
        logAction('roleService ', 'info', [$user]);

        if ($user->can('roles.view')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to see the Roles List.');
    }

    public function create(User $user): Response
    {
        if ($user->can('roles.create')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to create roles.');
    }

    public function delete(User $user, Role $role): Response
    {
        if ($user->can('roles.delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have role to delete this role.');
    }

    public function update(User $user, Role $role): Response
    {

        if ($user->can('roles.edit')) {
            return Response::allow();
        }

        return Response::deny('You do not have role to update this role.');
    }
}
