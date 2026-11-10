<?php

namespace App\Providers;

use App\Events\PermissionUpdated;
use App\Listeners\LogPermissionUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PermissionUpdated::class => [
            LogPermissionUpdated::class,
        ],
    ];
}
