<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SandboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DemoSandboxController extends Controller
{
    public function show(): View
    {
        return view('auth.demo');
    }

    public function start(Request $request, SandboxService $sandbox): RedirectResponse
    {
        $demoEmail = (string) config('demo.email', 'demo@subtracker.test');
        $configuredPassword = (string) config('demo.password', '');
        $secret = $configuredPassword !== '' ? $configuredPassword : Str::random(40);

        $demoUser = User::firstOrCreate(
            ['email' => $demoEmail],
            [
                'name' => 'Sandbox Demo',
                'password' => Hash::make($secret),
                'email_verified_at' => now(),
            ]
        );

        Auth::login($demoUser);
        $request->session()->regenerate();
        $sandbox->startSession($demoUser->id);

        return redirect()->route('dashboard');
    }
}
