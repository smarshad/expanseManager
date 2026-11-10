<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LoginService;
use App\Services\Auth\Otp\LoginOtpService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $bodyCss;

    public function __construct(protected LoginService $loginService, protected LoginOtpService $LoginOtpService)
    {
        $this->bodyCss = getAuthPageCss();
    }

    public function showLogin()
    {
        $bodyCss = $this->bodyCss;
        return view('newauth/login', compact('bodyCss'));
    }

    public function login(LoginRequest $request)
    {
        return $this->loginService->handleLogin($request);
    }

    public function showOtpForm(Request $request)
    {
        $bodyCss = $this->bodyCss;
        return view('authotp.verify-login-otp', compact('bodyCss'));
    }

    public function verifyOtp(OtpRequest $request)
    {
        return $this->LoginOtpService->verify($request);
    }
}
