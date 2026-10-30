<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailOtpController extends Controller
{
    /**
     * Handle verification of the submitted 6-digit OTP code.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        $validated = $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.size' => 'The verification code must be exactly 6 digits.',
            'otp.regex' => 'The verification code must contain numbers only.',
        ]);

        if (! $user->verifyEmailOtp($validated['otp'])) {
            return back()->withErrors([
                'otp' => 'Invalid or expired verification code. Please check your email or request a new code.',
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1')
            ->with('status', 'Your email address has been successfully verified! Welcome to SubShield.');
    }
}
