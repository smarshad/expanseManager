<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfLocked
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Log for debugging
        // \Log::info('CheckIfLocked triggered', [
        //     'user_id' => $user?->id,
        //     'session_locked' => $request->session()->get('locked', false),
        // ]);

        // If user is locked in database
        if ($user && $user->is_locked) {
            auth()->logout();
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your account has been locked by an administrator']);
        }

        // If user has session lock (local lock screen)
        if ($user && $request->session()->get('locked', false)) {
            // Allow access to lock routes only
            if (! $request->routeIs('lock.show') &&
                ! $request->routeIs('lock.unlock') &&
                ! $request->routeIs('lock.lock')) {
                return redirect()->route('lock.show');
            }
        }

        return $next($request);
    }
}
