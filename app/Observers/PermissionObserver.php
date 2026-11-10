<?php

namespace App\Observers;

use App\Events\PermissionUpdated;
use App\Events\PermissionDeleted;
use App\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        PermissionUpdated::dispatch(
            $permission,
            $permission->getOriginal(),
            $permission->getChanges()
        );
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(Permission $permission): void
    {
        PermissionDeleted::dispatch(
            $permission->id,
            $permission->name
        );
    }

    /**
     * Handle the Permission "restored" event.
     */
    public function restored(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Permission $permission): void
    {
        //
    }
}
