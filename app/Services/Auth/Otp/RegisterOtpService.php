<?php

namespace App\Services\Auth\Otp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class RegisterOtpService extends BaseOtpService
{

    protected function getRedirectRoute(): string
    {
        return 'register';
    }

    protected function onOtpVerified(array $users, Request $request)
    {
        $user = User::create([
            'email' => $users['email'],
            'name' => $users['name'],
            'mobile_no' => $users['mobile_no'],
            'password' => $users['password'], // password should already be hashed in controller
        ]);

        Auth::login($user);

        Log::info('User registered and logged in after OTP.', ['email' => $user->email]);

        return redirect()->route('dashboard1');
    }
}
