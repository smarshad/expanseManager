<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Str;
use App\Models\User;

class ResetPasswordService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function resetPassword($request)
    {
        $payload = $request->validated(); // from your FormRequest

        $status = Password::reset(
            $payload,
            function (User $user, string $password) {
                $this->updateUserPassword($user, $password);
                event(new PasswordReset($user));
            }
        );

        $this->logAttempt($request->ip(), $payload['email'] ?? null, $status);

        // Handle result
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully!');
        }

        // Otherwise map errors to friendly messages
        $message = $this->mapErrorMessage($status, $payload);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }

    /**
     * Update the user's password and remember token.
     */
    protected function updateUserPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();
    }

    protected function logAttempt(?string $ip, ?string $email, string $status): void
    {
        $context = [
            'email' => $email,
            'ip' => $ip,
            'status' => $status,
        ];

        if ($status === Password::PASSWORD_RESET) {
            $this->logger->info('Password reset SUCCESS', $context);
        } else {
            // Save an informational message then a warning for failed attempts.
            $this->logger->info('Password reset attempt', $context);
            $this->logger->warning('Password reset FAILED', $context);
        }
    }

    /**
     * Map Laravel's password broker status codes to human-readable error messages.
     */
    protected function mapErrorMessage(string $status, array $payload): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => $this->detectTokenProblem($payload),
            Password::INVALID_USER => 'Email not found. Please check the email address and try again.',
            Password::RESET_THROTTLED => 'Too many password reset attempts. Please wait before trying again.',
            default => 'Failed to reset password. Please try again or request a new reset link.',
        };
    }

    /**
     * Provide more granular explanations for token-related issues.
     */
    protected function detectTokenProblem(array $payload): string
    {
        if (empty($payload['token'])) {
            return 'Token is missing from the request. (e.g., the reset link was broken or missing the token parameter.)';
        }

        // You can query the DB directly for extra clarity:
        $reset = \DB::table('password_reset_tokens')->where('email', $payload['email'])->first();

        if (!$reset) {
            return 'Email mismatch — no reset request found for this email address.';
        }

        if (now()->diffInMinutes($reset->created_at) > config('auth.passwords.users.expire', 60)) {
            return 'Token expired — reset links are valid for only ' . config('auth.passwords.users.expire', 60) . ' minutes.';
        }

        // If token is stored hashed (default since Laravel 9)
        if (Hash::check($payload['token'], $reset->token) === false) {
            return 'Token is invalid — it may have been already used or altered (URL encoding issue or tampered token).';
        }

        // Fallback
        return 'Token is invalid or expired. Please request a new password reset link.';
    }
}
