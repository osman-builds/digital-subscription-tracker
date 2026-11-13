<?php

namespace App\Services;

use App\Models\Category;
use App\Models\UserSetting;

/**
 * Manages the lifecycle, expiration, and session-only settings of an isolated sandbox session.
 */
class SandboxService
{
    public const SESSION_ROOT = 'sandbox';

    public const KEY_EXPIRES_AT = 'sandbox.expires_at';

    public const KEY_USER_ID = 'sandbox.user_id';

    public const KEY_NEXT_ID = 'sandbox.next_id';

    public const KEY_CATEGORIES = 'sandbox.categories';

    public const KEY_SUBSCRIPTIONS = 'sandbox.subscriptions';

    private const KEY_BASE_CURRENCY = 'sandbox.settings.base_currency';

    private const KEY_DARK_MODE = 'sandbox.settings.dark_mode';

    private const KEY_ALERT_7 = 'sandbox.settings.alert_7_days';

    private const KEY_ALERT_3 = 'sandbox.settings.alert_3_days';

    private const KEY_SETTINGS = 'sandbox.settings';

    private const VIRTUAL_ID_START = 100001;

    private const DEFAULT_CURRENCY = 'MYR';

    public function __construct(private FxRateService $fx) {}

    public function startSession(int $userId): void
    {
        $ttlMinutes = max(1, (int) config('demo.sandbox_ttl_minutes', 60));

        session([
            self::SESSION_ROOT => [
                'active' => true,
                'user_id' => $userId,
                'expires_at' => now()->addMinutes($ttlMinutes)->timestamp,
                'next_id' => self::VIRTUAL_ID_START,
                'categories' => [],
                'subscriptions' => $this->buildSampleSubscriptions($userId),
                'settings' => [
                    'user_id' => $userId,
                    'base_currency' => self::DEFAULT_CURRENCY,
                    'alert_7_days' => false,
                    'alert_3_days' => false,
                    'dark_mode' => false,
                ],
            ],
        ]);
    }

    public function ensureInitialized(int $userId): void
    {
        if (! session()->has(self::KEY_EXPIRES_AT) || (int) session(self::KEY_USER_ID) !== $userId) {
            $this->startSession($userId);
        }
    }

    public function isExpired(): bool
    {
        $expiresAt = session(self::KEY_EXPIRES_AT);

        return $expiresAt !== null && now()->timestamp >= (int) $expiresAt;
    }

    public function remainingMinutes(): int
    {
        $expiresAt = (int) session(self::KEY_EXPIRES_AT, 0);

        return (int) max(0, ceil(($expiresAt - now()->timestamp) / 60));
    }

    public function clearSession(): void
    {
        session()->forget(self::SESSION_ROOT);
    }

    public function getSettings(int $userId): UserSetting
    {
        $this->ensureInitialized($userId);
        $raw = (array) session(self::KEY_SETTINGS, []);

        $setting = new UserSetting;
        $setting->forceFill([
            'user_id' => $userId,
            'base_currency' => $raw['base_currency'] ?? self::DEFAULT_CURRENCY,
            'alert_7_days' => false,
            'alert_3_days' => false,
            'dark_mode' => (bool) ($raw['dark_mode'] ?? false),
        ]);

        return $setting;
    }

    public function updatePreferences(int $userId, string $baseCurrency): void
    {
        $this->ensureInitialized($userId);
        session([
            self::KEY_BASE_CURRENCY => strtoupper($baseCurrency),
            self::KEY_ALERT_7 => false,
            self::KEY_ALERT_3 => false,
        ]);
    }

    public function updateDarkMode(int $userId, bool $darkMode): bool
    {
        $this->ensureInitialized($userId);
        session([self::KEY_DARK_MODE => $darkMode]);

        return $darkMode;
    }

    public function allocateId(): int
    {
        $nextId = (int) session(self::KEY_NEXT_ID, self::VIRTUAL_ID_START);
        session([self::KEY_NEXT_ID => $nextId + 1]);

        return $nextId;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildSampleSubscriptions(int $userId): array
    {
        $categoryMap = Category::whereNull('user_id')->pluck('id', 'name');
        $today = now();
        $cycleMonthly = 'monthly';
        $statusActive = 'active';

        $templates = [
            [
                'name' => 'Netflix',
                'description' => 'Streaming movies and TV shows',
                'amount' => 15.49,
                'currency' => 'USD',
                'billing_cycle' => $cycleMonthly,
                'start_date' => $today->copy()->subMonths(6)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(2)->toDateString(),
                'status' => $statusActive,
                'category_id' => $categoryMap['Entertainment'] ?? null,
                'website_url' => 'https://netflix.com',
                'logo_url' => null,
                'notes' => 'Sample sandbox subscription',
            ],
            [
                'name' => 'Spotify',
                'description' => 'Music streaming — EU plan',
                'amount' => 9.99,
                'currency' => 'EUR',
                'billing_cycle' => $cycleMonthly,
                'start_date' => $today->copy()->subMonths(8)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(5)->toDateString(),
                'status' => $statusActive,
                'category_id' => $categoryMap['Music'] ?? null,
                'website_url' => 'https://spotify.com',
                'logo_url' => null,
                'notes' => null,
            ],
            [
                'name' => 'GitHub Copilot',
                'description' => 'AI coding assistant',
                'amount' => 10.00,
                'currency' => 'USD',
                'billing_cycle' => $cycleMonthly,
                'start_date' => $today->copy()->subMonths(4)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(12)->toDateString(),
                'status' => $statusActive,
                'category_id' => $categoryMap['Software & Dev'] ?? null,
                'website_url' => 'https://github.com',
                'logo_url' => null,
                'notes' => null,
            ],
            [
                'name' => 'iCloud+',
                'description' => '2TB family cloud backup',
                'amount' => 44.90,
                'currency' => self::DEFAULT_CURRENCY,
                'billing_cycle' => $cycleMonthly,
                'start_date' => $today->copy()->subMonths(10)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(18)->toDateString(),
                'status' => $statusActive,
                'category_id' => $categoryMap['Cloud Storage'] ?? null,
                'website_url' => 'https://icloud.com',
                'logo_url' => null,
                'notes' => null,
            ],
            [
                'name' => 'Notion Plus',
                'description' => 'Workspace docs and wiki',
                'amount' => 96.00,
                'currency' => 'USD',
                'billing_cycle' => 'annual',
                'start_date' => $today->copy()->subMonths(5)->toDateString(),
                'next_billing_date' => $today->copy()->addMonths(7)->toDateString(),
                'status' => $statusActive,
                'category_id' => $categoryMap['Productivity'] ?? null,
                'website_url' => 'https://notion.so',
                'logo_url' => null,
                'notes' => null,
            ],
        ];

        $result = [];
        $id = self::VIRTUAL_ID_START - 500;
        foreach ($templates as $tpl) {
            $id++;
            $tpl['id'] = $id;
            $tpl['user_id'] = $userId;
            $tpl['amount_myr'] = $this->fx->convertForSandbox((float) $tpl['amount'], $tpl['currency'], self::DEFAULT_CURRENCY);
            $result[$id] = $tpl;
        }

        return $result;
    }
}
