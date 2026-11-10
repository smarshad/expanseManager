<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;


class UserPolicy
{

    public function viewAny(User $user): Response
    {
        \Log::info('User viewAny policy check', [
            'user_id' => $user->id,
            'can_view_all' => $user->can('users.view.all'),
            'can_view_own' => $user->can('users.view.own'),
            'is_super_admin' => $user->isSuperAdmin()
        ]);

        if ($user->can('users.view.all') || $user->can('users.view.own')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view users.');
    }


    public function view(User $currentUser, User $userToView): Response
    {
        \Log::info('User view policy check', [
            'current_user_id' => $currentUser->id,
            'target_user_id' => $userToView->id,
            'target_created_by' => $userToView->created_by,
            'can_view_all' => $currentUser->can('users.view.all'),
            'can_view_own' => $currentUser->can('users.view.own')
        ]);

        // Users can always view themselves
        if ($currentUser->id === $userToView->id) {
            return Response::allow('You can view your own profile.');
        }

        // Check super admin protection
        if ($userToView->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return Response::deny('You cannot view super admin accounts.');
        }

        // Can view all users
        if ($currentUser->can('users.view.all')) {
            return Response::allow();
        }

        // Can view own created users
        if ($currentUser->can('users.view.own')) {
            if ($currentUser->id === $userToView->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only view users you created.');
        }

        return Response::deny('You do not have permission to view this user.');
    }


    public function create(User $user): Response
    {
        \Log::info('User create policy check', [
            'user_id' => $user->id,
            'can_create' => $user->can('users.create')
        ]);

        if ($user->can('users.create')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to create users.');
    }

    public function viewPermission(User $user): Response
    {
        \Log::info('User viewPermission policy check', [
            'user_id' => $user->id,
            'can_view_permissions' => $user->can('view.users.permissions')
        ]);

        if ($user->can('view.users.permissions')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view user permissions.');
    }

    public function editPermission(User $currentUser, User $userToEdit): Response
    {
        \Log::info('User editPermission policy check', [
            'current_user_id' => $currentUser->id,
            'target_user_id' => $userToEdit->id,
            'can_edit_permissions' => $currentUser->can('edit.users.permissions'),
            'target_is_super_admin' => $userToEdit->isSuperAdmin()
        ]);

        // Prevent editing permissions of super admin unless you're also super admin
        if ($userToEdit->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return Response::deny('You cannot edit permissions of super admin accounts.');
        }

        if ($currentUser->can('edit.users.permissions')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to edit user permissions.');
    }

    public function delete(User $currentUser, User $userToDelete): Response
    {
        \Log::info('User delete policy check', [
            'current_user_id' => $currentUser->id,
            'target_user_id' => $userToDelete->id,
            'target_created_by' => $userToDelete->created_by,
            'target_is_super_admin' => $userToDelete->isSuperAdmin(),
            'can_delete_all' => $currentUser->can('users.delete.all'),
            'can_delete_own' => $currentUser->can('users.delete.own'),
            'is_super_admin' => $currentUser->isSuperAdmin()
        ]);

        // Prevent self-deletion
        if ($currentUser->id === $userToDelete->id) {
            return Response::deny('You cannot delete your own account.');
        }

        // Prevent deleting super admin unless you're also super admin
        if ($userToDelete->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return Response::deny('You cannot delete super admin accounts.');
        }

        // Can delete all users
        if ($currentUser->can('users.delete.all')) {
            return Response::allow();
        }

        // Can delete own created users
        if ($currentUser->can('users.delete.own')) {
            if ($currentUser->id === $userToDelete->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only delete users you created.');
        }

        return Response::deny('You do not have permission to delete this user.');
    }


    public function restore(User $currentUser, User $userToRestore): Response
    {
        \Log::info('User restore policy check', [
            'current_user_id' => $currentUser->id,
            'target_user_id' => $userToRestore->id
        ]);

        if ($currentUser->can('users.restore')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to restore users.');
    }

    public function forceDelete(User $currentUser, User $userToDelete): Response
    {
        \Log::info('User forceDelete policy check', [
            'current_user_id' => $currentUser->id,
            'target_user_id' => $userToDelete->id
        ]);

        if ($currentUser->can('users.force-delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to permanently delete users.');
    }

    public function update(User $currentUser, User $userToUpdate): Response
    {
        \Log::info('User update policy check', [
            'current_user_id' => $currentUser->id,
            'target_user_id' => $userToUpdate->id,
            'target_created_by' => $userToUpdate->created_by,
            'can_edit_all' => $currentUser->can('users.edit.all'),
            'can_edit_own' => $currentUser->can('users.edit.own'),
            'is_super_admin' => $currentUser->isSuperAdmin()
        ]);

        // Users can always update themselves
        // if ($currentUser->id === $userToUpdate->id) {
        //     return Response::allow('You can update your own profile.');
        // }

        // Prevent updating super admin unless you're also super admin
        if ($userToUpdate->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return Response::deny('You cannot update super admin accounts.');
        }

        // Can edit all users
        if ($currentUser->can('users.edit.all')) {
            return Response::allow();
        }

        // Can edit own created users
        if ($currentUser->can('users.edit.own')) {
            if ($currentUser->id === $userToUpdate->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only update users you created.');
        }

        return Response::deny('You do not have permission to update this user.');
    }
}
