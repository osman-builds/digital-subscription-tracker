<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SandboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, SandboxService $sandbox): RedirectResponse
    {
        $demoEmail = strtolower((string) config('demo.email', 'demo@subtracker.test'));
        if (strtolower((string) $request->input('email')) === $demoEmail && ! config('demo.allow_password_login')) {
            throw ValidationException::withMessages([
                'email' => 'Direct password login for the demo account is disabled. Please launch the 1-hour Interactive Sandbox instead.',
            ]);
        }

        $request->authenticate();

        $request->session()->regenerate();

        if (auth()->user()?->isSandbox()) {
            $sandbox->startSession(auth()->id());
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, SandboxService $sandbox): RedirectResponse
    {
        $sandbox->clearSession();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
