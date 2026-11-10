<?php

namespace App\Services\Auth\Otp;

use App\Models\EmailOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

abstract class BaseOtpService
{
    /**
     * Core OTP verification logic.
     *
     * @param  array   $data
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $users = $request->session()->get('users');
        // dd($users);

        if (!$this->hasValidSession($users)) {
            Log::warning(static::class . ': OTP verification attempted without valid session.', [
                'session_users' => $users,
            ]);

            return redirect()
                ->route($this->getRedirectRoute())
                ->with(['error' => 'Session expired or invalid. Please try again.']);
        }

        $email = $users['email'];
        $otp = EmailOtp::where('email', $email)->first();
      
        if (!$otp) {
            Log::error(static::class . ': No OTP found.', ['email' => $email]);
            return back()->withInput()->with(['error' => 'No OTP found. Please request a new one!']);
        }

        if ($this->isOtpExpired($otp)) {
            Log::error(static::class . ': OTP expired.', ['email' => $email]);
            return back()->withInput()->with(['error' => 'The OTP has expired. Please request a new one!']);
        }
        // dd($request);
        if ((string) $otp->otp !== trim((string) $request->otp)) {
            Log::error(static::class . ': Invalid OTP entered.', [
                'email' => $email,
                'otp_entered' => $request->otp,
            ]);
            return back()->withInput()->with(['error' => 'Invalid OTP entered.']);
        }

        // Delegate post-verification logic to the subclass
        $response = $this->onOtpVerified($users, $request);

        // Clean up
        $request->session()->forget('users');
        $otp->delete();

        Log::info(static::class . ': OTP verified successfully.', ['email' => $email]);

        return $response;
    }

    /**
     * Check if session data exists and is valid.
     */
    protected function hasValidSession(?array $users): bool
    {
        return isset($users['email'], $users['password']);
    }

    /**
     * Check if OTP is expired.
     */
    protected function isOtpExpired($otp): bool
    {
        return $otp->expired_at < Carbon::now();
    }

    /**
     * Route to redirect if session expired or invalid.
     */
    abstract protected function getRedirectRoute(): string;

    /**
     * Action to take after successful OTP verification.
     *
     * @param array   $users
     * @param Request $request
     */
    abstract protected function onOtpVerified(array $users, Request $request);
}
