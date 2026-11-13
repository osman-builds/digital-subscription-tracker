<?php

namespace App\Http\Controllers;

use App\Services\SandboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DemoSandboxController extends Controller
{
    public function show(): View
    {
        return view('auth.demo');
    }

    public function start(Request $request, SandboxService $sandbox): RedirectResponse
    {
        $virtualUser = SandboxService::makeVirtualUser();

        $request->session()->regenerate();
        $sandbox->startSession(SandboxService::SANDBOX_USER_ID);
        Auth::login($virtualUser);

        return redirect()->route('dashboard');
    }
}
