<?php

namespace App\Services\Auth;

use App\Mail\SendOtpMail;
use App\Models\EmailOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class RegisterService
{
    /**
     * Handle the service logic.
     */
    public function handle($request)
    {
        $otp_enabled = config('auth.otp_enabled', true);
        log::info('Otp enabled ', ['otp_enabled' => $otp_enabled]);

        if ($otp_enabled) {
            return $this->sendRegisterOtp($request);
        }
        return $this->registerUser($request);
    }

    public function registerUser($request) {
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password,
        ]);
        // unset($users['password']);
        Auth::login($user);
        Log::info('User successfully Registered direct', [$request]);
        return redirect()->route('dashboard1');
    }

    public function sendRegisterOtp($request)
    {
        // generate OTP
        // insert/update into email_otp table
        // set session
        // send Email
        // redirect
        try {
            $otp = generate_otp();
            $otp_expiry = (int) config('auth.otp_expiry', 10);
            $email = $request->email;
            EmailOtp::updateOrCreate([
                'email' => $email
            ], [
                'email'     => $email,
                'otp'       => $otp,
                'expired_at' => Carbon::now()->addMinutes($otp_expiry),
            ]);
            
            Mail::to($email)->send(new SendOtpMail($otp));
            session(['users' =>
            [
                'email'    => $email,
                'name'     => $request->name,
                'mobile_no' => $request->mobile_no,
                'password' => Hash::make($request->password)
            ]]);
            return redirect()->route('register.otp.show');
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('OTP email failed: ' . $e->getMessage());
    
            // Optional: show user-friendly message
            return back()->with('error', 'Failed to send OTP. Please try again later.');
        }
    }
}
