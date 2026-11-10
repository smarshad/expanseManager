<?php

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

if (!function_exists('createLogContext')) {
    function createLogContext(string $email, ?string $ip = null, ?int $userId = null): array
    {
        $ctx = [
            'email' => $email,
            'ip' => $ip,
        ];

        if ($userId !== null) {
            $ctx['user_id'] = $userId;
        }

        return $ctx;
    }
}
if (!function_exists('logAction')) {
    /**
     * Centralized logging function with context & level support.
     */
    function logAction(string $message, string $level = 'info', array $extra = []): void
    {
        $user = auth()->user();
        $email = $user?->email ?? 'guest';
        $ip = request()->ip();
        $userId = $user?->id;

        // Base context
        $context = createLogContext($email, $ip, $userId);

        // Merge any extra data
        $context = array_merge($context, $extra);

        // Send to log
        Log::$level($message, $context);
    }
}

if (!function_exists('generate_otp')) {
    /**
     * Generate a secure random numeric OTP.
     *
     * @param int $length
     * @return int
     */
    function generate_otp(int $length = 6): int
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return random_int($min, $max);
    }
}

if (!function_exists('getAuthPageCss')) {
    /**
     * @return string
     */

    function getAuthPageCss(): string
    {
        return 'class="authentication-bg bg-primary authentication-bg-pattern d-flex align-items-center pb-0 vh-100"';
    }
}

if (!function_exists('generate_uuid')) {
    /**
     * Generate a UUID (v4).
     *
     * @return string
     */

    function generate_uuid(): string
    {
        return (string) Str::uuid();
    }
}

if (!function_exists('generate_token')) {
    /**
     * Generate a random secure token.
     *
     * @param int $length
     * @return string
     */
    function generate_token(int $length = 40): string
    {
        return Str::random($length);
    }
}

if (!function_exists('mask_email')) {
    /**
     * Mask an email address (e.g., john****@gmail.com)
     *
     * @param string $email
     * @return string
     */
    function mask_email(string $email): string
    {
        [$name, $domain] = explode('@', $email);
        $masked = substr($name, 0, 3) . str_repeat('*', max(0, strlen($name) - 3));
        return $masked . '@' . $domain;
    }
}

if (!function_exists('mask_mobile')) {
    /**
     * Mask mobile number (e.g., 98******45)
     *
     * @param string $mobile
     * @return string
     */
    function mask_mobile(string $mobile): string
    {
        return substr($mobile, 0, 2) . str_repeat('*', strlen($mobile) - 4) . substr($mobile, -2);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date to human-readable string.
     *
     * @param string|\DateTimeInterface|null $date
     * @param string $format
     * @return string
     */
    function format_date($date, string $format = 'd M Y, h:i A'): string
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('array_get_safe')) {
    /**
     * Safely get array value with default.
     *
     * @param array $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    function array_get_safe(array $array, $key, $default = null)
    {
        return $array[$key] ?? $default;
    }
}
