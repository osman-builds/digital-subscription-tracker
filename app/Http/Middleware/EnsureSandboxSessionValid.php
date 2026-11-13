<?php

namespace App\Http\Middleware;

use App\Services\SandboxService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the 1-hour lifetime on sandbox sessions and initializes ephemeral session state.
 */
class EnsureSandboxSessionValid
{
    public function __construct(private SandboxService $sandbox) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user === null || ! $user->isSandbox()) {
            return $next($request);
        }

        if ($this->sandbox->isExpired()) {
            $this->sandbox->clearSession();
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('demo.show')
                ->with('status', 'Your 1-hour sandbox session has expired. Start a fresh sandbox anytime or create a free account.');
        }

        $this->sandbox->ensureInitialized($user->id);

        return $next($request);
    }
}
