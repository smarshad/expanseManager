<?php

namespace App\Listeners;

use App\Events\PermissionDeleted;

class LogPermissionDeleted
{
    /**
     * Handle the event.
     */
    public function handle(PermissionDeleted $event): void
    {
        logAction('Permission Deleted', 'warning', [
            'permission_id' => $event->permissionId,
            'old_name'      => $event->name,
        ]);
    }
}
