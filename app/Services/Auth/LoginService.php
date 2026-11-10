<?php

namespace App\Services\Auth;

use App\Jobs\SendMail;
use App\Mail\SendOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
class LoginService
{

    public function handleLogin($request)
    {
        // $validated = $request->validated();

        $user = User::where('email', $request->email)->first();

        if(!$user){
            Log::warning('Login Failed: Invalid Email',['email' => $request->email]);
            return redirect()->back()->withInput()->withErrors(['error' => 'Invalid Email']);
        }

        session(['users' =>[
            'email' => $request->email,
            'password' => $request->password
        ]]);

        return $user->enable_two_factor_auth ? $this->handleTwoFactor($request->email) : $this->authenticateUser($request);
    }

    protected function handleTwoFactor($email){ 
        $otp = random_int(100000, 999999);
        $expiry = (int) config('auth.otp_expiry', 10); // configurable from config/auth.php
        EmailOtp::updateOrCreate(['email' => $email], [
            'email' => $email,
            'otp'   => $otp,
            'expired_at' => Carbon::now()->addMinutes($expiry)
        ]);
        // \Log::info("Dispatching email to: {$email}");
        SendMail::dispatch($otp, 'OTP For Login', $email);
        // \Log::info("Job dispatched for email: {$email}");
        return redirect()->route('login.otp.verify');
    }

    protected function authenticateUser($request){
        $request->authenticate();
        $request->session()->regenerate();
        Log::info('User successfully logged in.', ['email' => $request->email]);
        return redirect()->intended(route('dashboard1', absolute: false));
    }
}
