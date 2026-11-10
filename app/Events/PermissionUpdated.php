<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use App\Models\Permission;

class PermissionUpdated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Permission $permission,
        public array $old,
        public array $changes
    )
    {
        //
    }

}
