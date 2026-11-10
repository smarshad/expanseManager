<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class DatabaseBackupPolicy
{
    public function create(User $user)
    {
        if ($user->can('create.db.backup')) {
            return Response::allow();
        }
        return Response::deny('You must be an Administrator or Editor to create a category.');
    }

    public function download(User $user)
    {
        if ($user->can('download.db.backup')) {
            return Response::allow();
        }
        return Response::deny('You must be an Administrator or Editor to create a category.');
    }
}
