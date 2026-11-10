<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Exception;

class ProfileService
{
    /**
     * Toggle Two-Factor Authentication for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleTwoFactorAuthentication(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            Log::warning('Two-Factor toggle failed: Authenticated user not found.', [
                'ip' => $request->ip(),
            ]);

            return redirect()
                ->route('profile.edit')
                ->with('error', 'User not found.');
        }

        try {
            // Toggle the flag (invert the current value)
            $user->enable_two_factor_auth = $user->enable_two_factor_auth ? 0 : 1;
            $user->save();

            $statusText = $user->enable_two_factor_auth ? 'Enabled' : 'Disabled';

            Log::info('Two-Factor Authentication status changed successfully.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => $statusText,
                'ip' => $request->ip(),
            ]);

            return redirect()
                ->route('profile.edit')
                ->with('success', "Two-Factor Authentication <b>{$statusText}</b> successfully.");

        } catch (Exception $e) {
            Log::error('Error while toggling Two-Factor Authentication.', [
                'user_id' => $user->id ?? null,
                'email' => $user->email ?? null,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while updating Two-Factor Authentication.');
        }
    }
}
