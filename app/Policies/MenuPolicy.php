<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Menu;
use Illuminate\Auth\Access\Response;

class MenuPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user): Response
    {

        if ($user->can('menu.view.all') || $user->can('menu.view.own')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to see the Menu List.');
    }

    public function create(User $user): Response
    {
        if ($user->can('menu.create')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to create Menu.');
    }

    public function delete(User $user, Menu $menu): Response
    {
        logAction('delete', 'info', ['user-id' => $user->id, 'created-by' => $menu->created_by, 'delete-all' => $user->can('menu.delete.all'), 'delete-own' => $user->can('menu.delete.own')]);
        // logAction('menu', 'info', [$menu]);
        if ($user->can('menu.delete.all')) {
            return Response::allow();
        }

        if ($user->can('menu.delete.own')) {
            if ($user->id === $menu->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only delete menu you created.');
        }
        return Response::deny('You do not have permission to delete this Menu.');
    }

    public function update(User $user, Menu $menu): Response
    {
        logAction('delete', 'info', ['user-id' => $user->id, 'created-by' => $menu->created_by, 'delete-all' => $user->can('menu.delete.all'), 'delete-own' => $user->can('menu.delete.own')]);

        if ($user->can('menu.edit.all')) {
            return Response::allow();
        }

        if ($user->can('menu.edit.own')) {
            if ($user->id === $menu->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only update menu you created.');
        }
        return Response::deny('You do not have permission to update this Menu.');
    }
}
