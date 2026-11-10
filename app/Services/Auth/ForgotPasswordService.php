<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPasswordService
{
    /**
     * Handle the service logic.
     */
    public function sentLink($request)
    {
        $email = $request->email;

        $logContext = createLogContext($email, $request->ip());
        if ($email === '') {
            log::warning('Password reset attempted with empty email.', $logContext);
            return redirect()->back()->with(['error' => 'Empty Email']);
        }

        $user = $this->findUserByEmail($email);

        if (!$user) {
            Log::error('Password reset link not sent since email not exists', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);
            return redirect()->back()->with(['error' => 'Email not exists']);
        }

        if ($this->isLocked($user)) {
            Log::warning('Password reset blocked: user locked.', createLogContext($email, $request->ip(), $user->id));

            return redirect()->back()->with(['error' => 'You account is locked please contact with our team']);
        }

        return $this->sentPasswordLink($request, $logContext);
    }

    /**
     * Find a user by email.
     */
    protected function findUserByEmail(string $email): ?User
    {
        return User::whereEmail($email)->first();
    }

    /**
     * Whether the given user account is locked.
     */
    protected function isLocked(User $user): bool
    {
        // Assumes boolean casting; adjust if stored differently
        return (bool) ($user->is_locked ?? false);
    }

    protected function sentPasswordLink($request, $logContext)
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset link sent.', $logContext + ['status' => $status]);
            return back()->with('status', 'A password reset link has been sent to your email.');
        }

        log::error('Failed to send password reset link.', $logContext + ['status' => $status]);
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Error while Sent reset password link']);
    }

    
}
