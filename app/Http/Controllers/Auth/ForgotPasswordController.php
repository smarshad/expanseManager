<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\ForgotPasswordService;

final class ForgotPasswordController extends Controller
{
    public function __construct(protected ForgotPasswordService $service) {}

    public function show(): View
    {
        $bodyCss = getAuthPageCss();
        return view('newauth.password.forgot-password', compact('bodyCss'));
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        return $this->service->sentLink($request);
    }
}
