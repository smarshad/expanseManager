<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;

class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Move current login to last login
        $user->last_login_at = $user->current_login_at;

        // Set new login time
        $user->current_login_at = now();

        $user->save();

        // Save login history
        LoginHistory::create([
            'user_id'    => $user->id,
            'login_at'   => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
