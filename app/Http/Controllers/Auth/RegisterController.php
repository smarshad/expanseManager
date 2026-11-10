<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use App\Services\Auth\Otp\RegisterOtpService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\OtpRequest;
class RegisterController extends Controller
{

    public function __construct(protected RegisterService $registerService, protected RegisterOtpService $registerOtpService) {}

    public function showRegister()
    {
        $bodyCss = getAuthPageCss();
        return view('newauth/register', compact('bodyCss'));
    }

    public function showOtpForm()
    {
        $bodyCss = getAuthPageCss();
        return view('authotp/verify-register-otp', compact('bodyCss'));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        return $this->registerService->handle($request);
    }

    public function verifyOtpAndCreateUser(OtpRequest $request){
        return $this->registerOtpService->verify($request);
    }
}
