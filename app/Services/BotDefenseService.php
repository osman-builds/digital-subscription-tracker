<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotDefenseService
{
    private const JAIL_PREFIX = 'bot_defense:jailed:';

    private const VIOLATION_PREFIX = 'bot_defense:violations:';

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private const TURNSTILE_FIELD = 'cf-turnstile-response';

    private const SCRIPT_USER_AGENTS_REGEX = '/(python-requests|python-urllib|aiohttp|httpx|curl\/|go-http-client|scrapy|libwww-perl)/i';

    public const MAX_VIOLATIONS = 3;

    public const VIOLATION_TTL_SECONDS = 120;

    public const JAIL_TTL_SECONDS = 900;

    public function isIpJailed(string $ip): bool
    {
        return Cache::has(self::JAIL_PREFIX.$ip);
    }

    public function recordViolation(string $ip): void
    {
        $violationKey = self::VIOLATION_PREFIX.$ip;
        $count = (int) Cache::get($violationKey, 0) + 1;
        Cache::put($violationKey, $count, self::VIOLATION_TTL_SECONDS);

        if ($count >= self::MAX_VIOLATIONS) {
            Cache::put(self::JAIL_PREFIX.$ip, true, self::JAIL_TTL_SECONDS);
        }
    }

    public function isSuspiciousScript(Request $request): bool
    {
        $userAgent = (string) $request->header('User-Agent', '');
        if ($userAgent === '') {
            return false;
        }

        return (bool) preg_match(self::SCRIPT_USER_AGENTS_REGEX, $userAgent);
    }

    public function isDeviceTokenTampered(?string $rawToken): bool
    {
        if ($rawToken === null || trim($rawToken) === '') {
            return false;
        }

        $decoded = base64_decode($rawToken, true);
        $data = $decoded !== false ? json_decode($decoded, true) : null;

        return ! is_array($data)
            || ! isset($data['sw'], $data['sh'], $data['tz'])
            || ! is_numeric($data['sw'])
            || ! is_numeric($data['sh'])
            || ! is_numeric($data['tz']);
    }

    public function verifyTurnstile(Request $request, string $secretKey, ?string $expectedAction): bool
    {
        $token = (string) $request->input(self::TURNSTILE_FIELD, '');
        if ($token === '') {
            return false;
        }

        try {
            $response = Http::withOptions(['force_ip_resolve' => 'v4'])
                ->connectTimeout(5)
                ->timeout(8)
                ->asForm()
                ->post(self::VERIFY_URL, [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            $verified = $response->successful()
                && (bool) $response->json('success', false)
                && $this->matchesAllowedHostnames((string) $response->json('hostname', ''))
                && $this->matchesExpectedAction((string) $response->json('action', ''), $expectedAction);

            if (! $verified) {
                Log::warning('Turnstile verification rejected', [
                    'status' => $response->status(),
                    'error_codes' => $response->json('error-codes', []),
                    'hostname' => (string) $response->json('hostname', ''),
                    'action' => (string) $response->json('action', ''),
                    'expected_action' => $expectedAction,
                ]);
            }

            return $verified;
        } catch (\Throwable $e) {
            Log::warning('Turnstile siteverify request failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function matchesAllowedHostnames(string $hostname): bool
    {
        $allowedHostnames = (array) config('services.turnstile.hostnames', []);
        if ($allowedHostnames === []) {
            return true;
        }

        if (app()->environment('local')) {
            $allowedHostnames = array_merge($allowedHostnames, ['localhost', '127.0.0.1']);
        }

        return in_array(strtolower($hostname), array_map('strtolower', $allowedHostnames), true);
    }

    public function matchesExpectedAction(string $actualAction, ?string $expectedAction): bool
    {
        if ($expectedAction === null || $expectedAction === '') {
            return true;
        }

        return $actualAction === $expectedAction;
    }
}
