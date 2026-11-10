<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Auth\Access\Response;

class PaymentMethodPolicy
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

        if ($user->can('paymentmethod.view.all') || $user->can('paymentmethod.view.own')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to see the PaymentMethod List.');
    }

    public function create(User $user): Response
    {
        if ($user->can('paymentmethod.create')) {
            return Response::allow();
        }
        return Response::deny('You do not have permission to create PaymentMethod.');
    }

    public function delete(User $user, PaymentMethod $paymentMethod): Response
    {
        // logAction('delete', 'info', ['user-id' => $user->id, 'created-by' => $paymentMethod->created_by, 'delete-all' => $user->can('paymentmethod.delete.all'), 'delete-own' => $user->can('paymentmethod.delete.own')]);
        // logAction('paymentmethod', 'info', [$paymentMethod]);
        if ($user->can('paymentmethod.delete.all')) {
            return Response::allow();
        }

        if ($user->can('paymentmethod.delete.own')) {
            if ($user->id === $paymentMethod->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only delete paymentmethod you created.');
        }
        return Response::deny('You do not have permission to delete this PaymentMethod.');
    }

    public function update(User $user, PaymentMethod $paymentMethod): Response
    {
        logAction('delete', 'info', ['user-id' => $user->id, 'created-by' => $paymentMethod->created_by, 'delete-all' => $user->can('paymentmethod.delete.all'), 'delete-own' => $user->can('paymentmethod.delete.own')]);

        if ($user->can('paymentmethod.edit.all')) {
            return Response::allow();
        }

        if ($user->can('paymentmethod.edit.own')) {
            if ($user->id === $paymentMethod->created_by) {
                return Response::allow();
            }
            return Response::deny('You can only update paymentmethod you created.');
        }
        return Response::deny('You do not have permission to update this PaymentMethod.');
    }
}
