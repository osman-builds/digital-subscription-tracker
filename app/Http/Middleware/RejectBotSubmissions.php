<?php

namespace App\Http\Middleware;

use App\Services\BotDefenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject automated bot form submissions using script signature detection,
 * temporary IP jailing, invisible honeypot traps, client hardware token validation,
 * and Cloudflare Turnstile verification.
 */
class RejectBotSubmissions
{
    private const HONEYPOT_FIELD = 'website_hp';

    private const DEVICE_TOKEN_FIELD = 'client_device_token';

    private const ERR_BOT_FAILED = 'Unable to process this submission.';

    private const ERR_TURNSTILE_FAILED = 'Bot verification failed. Please complete the challenge and try again.';

    private const MSG_ACCESS_RESTRICTED = 'Access temporarily restricted due to suspicious automated activity.';

    private const MSG_AUTOMATED_SCRIPT = 'Forbidden: Automated script activity detected.';

    public function __construct(
        private readonly BotDefenseService $botDefense
    ) {}

    public function handle(Request $request, Closure $next, ?string $expectedAction = null): Response
    {
        $blockedResponse = $this->checkBotViolations($request, $expectedAction);
        if ($blockedResponse !== null) {
            return $blockedResponse;
        }

        return $next($request);
    }

    private function checkBotViolations(Request $request, ?string $expectedAction): ?Response
    {
        $ip = (string) $request->ip();

        $instantBlock = $this->checkInstantBlocks($request, $ip);
        if ($instantBlock !== null) {
            return $instantBlock;
        }

        return $this->checkFormBotTriggers($request, $ip, $expectedAction);
    }

    private function checkInstantBlocks(Request $request, string $ip): ?Response
    {
        if ($this->botDefense->isIpJailed($ip)) {
            return response(self::MSG_ACCESS_RESTRICTED, 429);
        }

        if ($this->botDefense->isSuspiciousScript($request)) {
            $this->botDefense->recordViolation($ip);

            return response(self::MSG_AUTOMATED_SCRIPT, 403);
        }

        return null;
    }

    private function checkFormBotTriggers(Request $request, string $ip, ?string $expectedAction): ?Response
    {
        if ($request->filled(self::HONEYPOT_FIELD) || $this->botDefense->isDeviceTokenTampered($request->input(self::DEVICE_TOKEN_FIELD))) {
            $this->botDefense->recordViolation($ip);

            return $this->backWithBotError($request, self::ERR_BOT_FAILED);
        }

        if ($this->failsTurnstile($request, $expectedAction)) {
            $this->botDefense->recordViolation($ip);

            return $this->backWithBotError($request, self::ERR_TURNSTILE_FAILED);
        }

        return null;
    }

    private function failsTurnstile(Request $request, ?string $expectedAction): bool
    {
        $siteKey = (string) config('services.turnstile.site_key', '');
        $secretKey = (string) config('services.turnstile.secret_key', '');

        if ($siteKey === '' || $secretKey === '') {
            return false;
        }

        return ! $this->botDefense->verifyTurnstile($request, $secretKey, $expectedAction);
    }

    private function backWithBotError(Request $request, string $errorMessage): Response
    {
        return back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->withErrors(['email' => $errorMessage]);
    }
}
