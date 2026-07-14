<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        session(['math_captcha_result' => $num1 + $num2]);

        return view('auth.login', compact('num1', 'num2'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Determine if onboarding tour should be shown for pelanggan role.
        // Special case: pelanggan@laundryan.com always shows onboarding (for developer testing).
        // For all other pelanggan accounts: show only if onboarding has never been completed.
        if ($user->role === 'pelanggan') {
            if ($user->email === 'pelanggan@laundryan.com') {
                // Always show onboarding for the developer testing account
                session(['show_onboarding' => true]);
            } elseif (is_null($user->onboarding_completed_at)) {
                // First-time login: onboarding not yet completed
                session(['show_onboarding' => true]);
            } else {
                // Returning user: onboarding already completed, do not show
                session(['show_onboarding' => false]);
            }
        }

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user->role === 'karyawan') {
            return redirect()->intended(route('karyawan.dashboard', absolute: false));
        } elseif ($user->role === 'kurir') {
            return redirect()->intended(route('kurir.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            \App\Models\ActivityLog::log('Auth & Security', 'Logout', 'User "' . $user->name . '" logged out', 'Auth', null, null, null, $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
