<?php

namespace App\Listeners;

use App\Events\PermissionUpdated;

class LogPermissionUpdated
{
    
    /**
     * Handle the event.
     */
    public function handle(PermissionUpdated $event)
    {
        // logger()->info('Listener hit');
        logAction('Permission Updated', 'info', [
            'permission_id' => $event->permission->id,
            'old'           => $event->old,
            'changes'       => $event->changes,
        ]);
    }
}
