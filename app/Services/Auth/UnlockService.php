<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;

class UnlockService
{
    /**
     * UnlockService constructor.
     */
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Handle unlocking of a locked user session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unlock(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Context for log entries
        $context = [
            'email' => $user?->email,
            'ip' => $request->ip(),
        ];

        // Validate user and password
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            $this->logger->warning('Unlock attempt failed: invalid password.', $context);

            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Clear lock flag and regenerate session
        $this->clearLockSession($request);

        // Redirect to intended URL or fallback
        $intendedUrl = $request->session()->pull('locked_intended_url', url('/'));

        return redirect()->to($intendedUrl);
    }

    /**
     * Clear lock session data and regenerate session ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearLockSession(Request $request): void
    {
        $session = $request->session();

        if ($session->has('locked')) {
            $session->forget('locked');
        }

        // Regenerate session ID to prevent fixation attacks
        $session->regenerate();

        $this->logger->debug('Lock session data cleared and session regenerated.', [
            'session_id' => $session->getId(),
        ]);
    }
}
