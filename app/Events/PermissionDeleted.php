<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use App\Models\Permission;

class PermissionDeleted
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $permissionId,
        public string $name
    )
    {
        //
    }

}
