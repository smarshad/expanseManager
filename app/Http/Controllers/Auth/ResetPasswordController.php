<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Services\Auth\ResetPasswordService;

final class ResetPasswordController extends Controller
{
    public function __construct(protected ResetPasswordService $service) {}

    public function show(Request $request): View
    {
        $bodyCss = getAuthPageCss();
        return view('newauth.password.reset-password', compact('bodyCss', 'request'));
    }

    public function update(PasswordResetRequest $request): RedirectResponse
    {
        return $this->service->resetPassword($request);
    }
}
