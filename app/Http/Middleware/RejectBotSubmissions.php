<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject automated bot form submissions using an invisible honeypot field
 * and optional Cloudflare Turnstile verification when keys are configured.
 */
class RejectBotSubmissions
{
    private const HONEYPOT_FIELD = 'website_hp';

    private const TURNSTILE_FIELD = 'cf-turnstile-response';

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled(self::HONEYPOT_FIELD)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Unable to process this submission.']);
        }

        $siteKey = (string) config('services.turnstile.site_key', '');
        $secretKey = (string) config('services.turnstile.secret_key', '');

        if ($siteKey !== '' && $secretKey !== '' && ! $this->verifyTurnstile($request, $secretKey)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Bot verification failed. Please complete the challenge and try again.']);
        }

        return $next($request);
    }

    private function verifyTurnstile(Request $request, string $secretKey): bool
    {
        $token = (string) $request->input(self::TURNSTILE_FIELD, '');
        if ($token === '') {
            return false;
        }

        try {
            $response = Http::timeout(3)->asForm()->post(self::VERIFY_URL, [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return $response->successful()
                && (bool) $response->json('success', false)
                && $this->matchesAllowedHostnames((string) $response->json('hostname', ''));
        } catch (\Throwable) {
            return false;
        }
    }

    private function matchesAllowedHostnames(string $hostname): bool
    {
        $allowedHostnames = (array) config('services.turnstile.hostnames', []);
        if ($allowedHostnames === []) {
            return true;
        }

        return in_array(strtolower($hostname), array_map('strtolower', $allowedHostnames), true);
    }
}
