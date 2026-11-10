<?php

namespace App\Services\Auth\Otp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginOtpService extends BaseOtpService
{
    
    protected function getRedirectRoute(): string
    {
        return 'login';
    }

    protected function onOtpVerified(array $users, Request $request)
    {
        $credentials = [
            'email' => $users['email'],
            'password' => $users['password'],
        ];

        if (!Auth::attempt($credentials)) {
            Log::error('Login failed after OTP verification.', ['email' => $users['email']]);
            return back()->withInput()->with(['error' => 'Invalid Email or Password.']);
        }

        Log::info('User logged in successfully after OTP.', ['email' => $users['email']]);

        return redirect()->route('dashboard1');
    }
}
