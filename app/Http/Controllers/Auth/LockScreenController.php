<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\UnlockRequest;
use App\Services\Auth\UnlockService;
class LockScreenController extends Controller
{

    public function __construct(protected UnlockService $unlockService)
    {
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $bodyCss = getAuthPageCss();
        // If globally locked, redirect to login with message
        if ($user->is_locked) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been locked by an administrator.',
            ]);
        }

        return view('newauth.lock', compact('user', 'bodyCss'));
    }

    public function lock(Request $request)
    {
        $request->session()->put('locked', true);
        $request->session()->put('locked_intended_url', url()->previous());

        return redirect()->route('lock.show');
    }

    public function unlock(UnlockRequest $request)
    {
        return $this->unlockService->unlock($request);
    }

    // Optional: Admin-only lock/unlock actions
    public function adminLock($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $user->update(['is_locked' => true]);
        return back()->with('status', "User {$user->name} locked successfully.");
    }

    public function adminUnlock($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $user->update(['is_locked' => false]);
        return back()->with('status', "User {$user->name} unlocked successfully.");
    }
}
