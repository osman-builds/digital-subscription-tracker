<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces an absolute session lifetime (e.g. 12 hours even if kept open all day)
 * and cryptographic session-to-browser & session-to-user binding to neutralize stolen cookies.
 */
class EnforceSessionSecurity
{
    public const KEY_STARTED_AT = 'auth_session_started_at';

    public const KEY_UA_HASH = 'auth_session_ua_hash';

    public const KEY_OWNER_MAC = 'auth_session_owner_mac';

    /**
     * Stamp a freshly authenticated session with start time and HMAC bindings.
     */
    public static function stampSession(Request $request, User $user): void
    {
        $request->session()->put([
            self::KEY_STARTED_AT => now()->timestamp,
            self::KEY_UA_HASH => self::computeUserAgentHash($request),
            self::KEY_OWNER_MAC => self::computeOwnerMac((int) $user->id),
        ]);
    }

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $session = $request->session();

        if (! $session->has(self::KEY_STARTED_AT) || ! $session->has(self::KEY_UA_HASH)) {
            $session->put([
                self::KEY_STARTED_AT => now()->timestamp,
                self::KEY_UA_HASH => self::computeUserAgentHash($request),
            ]);

            if ($this->isSessionBackedLogin($request) && ! $session->has(self::KEY_OWNER_MAC)) {
                $session->put(self::KEY_OWNER_MAC, self::computeOwnerMac((int) $user->id));
            }

            return $next($request);
        }

        if ($this->isSessionBackedLogin($request) && ! $session->has(self::KEY_OWNER_MAC)) {
            $session->put(self::KEY_OWNER_MAC, self::computeOwnerMac((int) $user->id));
        }

        $violation = $this->detectViolation($request, $user);

        if ($violation !== null) {
            return $this->terminateSession($request, $user, $violation['reason'], $violation['message']);
        }

        return $next($request);
    }

    /**
     * @return array{reason: string, message: string}|null
     */
    private function detectViolation(Request $request, User $user): ?array
    {
        $session = $request->session();

        if ($session->has(self::KEY_OWNER_MAC)) {
            $storedOwnerMac = (string) $session->get(self::KEY_OWNER_MAC, '');
            $expectedOwnerMac = self::computeOwnerMac((int) $user->id);

            if (! hash_equals($expectedOwnerMac, $storedOwnerMac)) {
                return [
                    'reason' => 'session_owner_mismatch',
                    'message' => 'Your session could not be verified. Please sign in again.',
                ];
            }
        }

        $storedUaHash = (string) $session->get(self::KEY_UA_HASH, '');
        $currentUaHash = self::computeUserAgentHash($request);

        if (! hash_equals($storedUaHash, $currentUaHash)) {
            return [
                'reason' => 'session_fingerprint_mismatch',
                'message' => 'Your session was invalidated due to a browser security mismatch. Please sign in again.',
            ];
        }

        $maxHours = max(1, (int) config('session.absolute_lifetime_hours', 12));
        $startedAt = (int) $session->get(self::KEY_STARTED_AT, 0);

        if (! $user->isSandbox() && (now()->timestamp - $startedAt) > ($maxHours * 3600)) {
            return [
                'reason' => 'absolute_session_expired',
                'message' => 'For your security, your session has expired. Please sign in again.',
            ];
        }

        return null;
    }

    private function terminateSession(Request $request, User $user, string $reason, string $message): Response
    {
        Log::warning('Authenticated session terminated by security policy', [
            'user_id' => $user->id,
            'reason' => $reason,
            'ip' => $request->ip(),
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('login')->with('status', $message);
    }

    private function isSessionBackedLogin(Request $request): bool
    {
        return $request->session()->has(Auth::guard('web')->getName());
    }

    private static function computeUserAgentHash(Request $request): string
    {
        $ua = strtolower(trim((string) $request->userAgent()));

        return hash_hmac('sha256', 'ua:'.$ua, (string) config('app.key'));
    }

    private static function computeOwnerMac(int $userId): string
    {
        return hash_hmac('sha256', 'uid:'.$userId, (string) config('app.key'));
    }
}
