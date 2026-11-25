<?php

namespace App\Services;

use App\Models\BillingEvent;
use App\Models\Subscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Manages the lifecycle, expiration, and session-only settings of an isolated sandbox session.
 */
class SandboxService
{
    public const SANDBOX_USER_ID = 0;

    public const SESSION_ROOT = 'sandbox';

    public const KEY_EXPIRES_AT = 'sandbox.expires_at';

    public const KEY_USER_ID = 'sandbox.user_id';

    public const KEY_NEXT_ID = 'sandbox.next_id';

    public const KEY_CATEGORIES = 'sandbox.categories';

    public const KEY_SUBSCRIPTIONS = 'sandbox.subscriptions';

    public const KEY_PRICE_HISTORIES = 'sandbox.price_histories';

    public const KEY_BILLING_EVENTS = 'sandbox.billing_events';

    public const SOURCE_AI_SCAN = 'ai_scan';

    public const SOURCE_RECURRING = 'recurring_cycle';

    public const SOURCE_MANUAL = 'manual_entry';

    private const KEY_BASE_CURRENCY = 'sandbox.settings.base_currency';

    private const KEY_MONTHLY_BUDGET = 'sandbox.settings.monthly_budget';

    private const KEY_BUDGET_ALERT_PERCENT = 'sandbox.settings.budget_alert_percent';

    private const KEY_DARK_MODE = 'sandbox.settings.dark_mode';

    private const KEY_ALERT_7 = 'sandbox.settings.alert_7_days';

    private const KEY_ALERT_3 = 'sandbox.settings.alert_3_days';

    private const KEY_SETTINGS = 'sandbox.settings';

    private const VIRTUAL_ID_START = 100001;

    private const DEFAULT_CURRENCY = 'MYR';

    private const DEFAULT_BUDGET_ALERT_PERCENT = 80;

    public function __construct(
        private FxRateService $fx,
        private SandboxSampleFactory $sampleFactory
    ) {}

    public static function makeVirtualUser(): User
    {
        $user = new User;
        $user->forceFill([
            'id' => self::SANDBOX_USER_ID,
            'name' => 'Sandbox Demo',
            'email' => (string) config('demo.email', 'demo@subtracker.test'),
            'email_verified_at' => now(),
        ]);

        return $user;
    }

    public function startSession(int $userId): void
    {
        $ttlMinutes = max(1, (int) config('demo.sandbox_ttl_minutes', 60));
        $sampleSubs = $this->sampleFactory->buildSubscriptions($userId);
        $samplePriceHistories = $this->sampleFactory->buildPriceHistories($sampleSubs);
        $sampleBillingEvents = $this->sampleFactory->buildBillingEvents($sampleSubs, $userId);

        session([
            self::SESSION_ROOT => [
                'active' => true,
                'user_id' => $userId,
                'expires_at' => now()->addMinutes($ttlMinutes)->timestamp,
                'next_id' => self::VIRTUAL_ID_START,
                'categories' => [],
                'subscriptions' => $sampleSubs,
                'price_histories' => $samplePriceHistories,
                'billing_events' => $sampleBillingEvents,
                'settings' => [
                    'user_id' => $userId,
                    'base_currency' => self::DEFAULT_CURRENCY,
                    'monthly_budget' => 520.00,
                    'budget_alert_percent' => self::DEFAULT_BUDGET_ALERT_PERCENT,
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
            'monthly_budget' => $raw['monthly_budget'] ?? null,
            'budget_alert_percent' => (int) ($raw['budget_alert_percent'] ?? self::DEFAULT_BUDGET_ALERT_PERCENT),
            'alert_7_days' => false,
            'alert_3_days' => false,
            'dark_mode' => (bool) ($raw['dark_mode'] ?? false),
        ]);

        return $setting;
    }

    public function updatePreferences(
        int $userId,
        string $baseCurrency,
        ?float $monthlyBudget = null,
        int $budgetAlertPercent = self::DEFAULT_BUDGET_ALERT_PERCENT
    ): void {
        $this->ensureInitialized($userId);
        session([
            self::KEY_BASE_CURRENCY => strtoupper($baseCurrency),
            self::KEY_MONTHLY_BUDGET => $monthlyBudget,
            self::KEY_BUDGET_ALERT_PERCENT => max(50, min(100, $budgetAlertPercent)),
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

    /** @return Collection<int, SubscriptionPriceHistory> */
    public function getPriceHistories(int $userId, ?int $subscriptionId = null): Collection
    {
        $this->ensureInitialized($userId);
        $raw = (array) session(self::KEY_PRICE_HISTORIES, []);

        $collection = collect($raw)->map(function (array $item): SubscriptionPriceHistory {
            $history = new SubscriptionPriceHistory;
            $history->forceFill([
                'id' => $item['id'] ?? null,
                'subscription_id' => (int) $item['subscription_id'],
                'old_amount' => (float) $item['old_amount'],
                'new_amount' => (float) $item['new_amount'],
                'currency' => $item['currency'],
                'change_amount' => (float) $item['change_amount'],
                'change_percent' => (float) $item['change_percent'],
                'detected_via' => $item['detected_via'] ?? self::SOURCE_AI_SCAN,
                'recorded_at' => isset($item['recorded_at']) ? Carbon::parse($item['recorded_at']) : now(),
            ]);

            return $history;
        });

        if ($subscriptionId !== null) {
            $collection = $collection->where('subscription_id', $subscriptionId)->values();
        }

        return $collection->sortByDesc('recorded_at')->values();
    }

    /**
     * @param  Collection<int, Subscription>|null  $subscriptions
     * @return Collection<int, BillingEvent>
     */
    public function getBillingEvents(int $userId, ?int $subscriptionId = null, ?Collection $subscriptions = null): Collection
    {
        $this->ensureInitialized($userId);
        $raw = (array) session(self::KEY_BILLING_EVENTS, []);
        $subsById = ($subscriptions ?? collect())->keyBy('id');

        $collection = collect($raw)->map(function (array $item) use ($subsById): BillingEvent {
            $event = new BillingEvent;
            $event->forceFill([
                'id' => $item['id'] ?? null,
                'user_id' => (int) $item['user_id'],
                'subscription_id' => (int) $item['subscription_id'],
                'amount' => (float) $item['amount'],
                'currency' => $item['currency'],
                'fx_rate_snapshot' => (float) $item['fx_rate_snapshot'],
                'amount_base' => (float) $item['amount_base'],
                'base_currency' => $item['base_currency'],
                'billing_cycle' => $item['billing_cycle'],
                'billed_at' => isset($item['billed_at']) ? Carbon::parse($item['billed_at']) : now(),
                'source' => $item['source'] ?? self::SOURCE_RECURRING,
            ]);

            $subId = (int) $item['subscription_id'];
            if ($subsById->has($subId)) {
                $event->setRelation('subscription', $subsById->get($subId));
            }

            return $event;
        });

        if ($subscriptionId !== null) {
            $collection = $collection->where('subscription_id', $subscriptionId)->values();
        }

        return $collection->sortByDesc('billed_at')->values();
    }

    public function recordPriceHistory(
        int $userId,
        int $subscriptionId,
        float $oldAmount,
        float $newAmount,
        string $currency,
        string $detectedVia = self::SOURCE_AI_SCAN
    ): void {
        $this->ensureInitialized($userId);
        $histories = (array) session(self::KEY_PRICE_HISTORIES, []);
        $changeAmount = round($newAmount - $oldAmount, 2);
        $changePercent = $oldAmount > 0 ? round(($changeAmount / $oldAmount) * 100, 2) : 0.0;

        $histories[] = [
            'id' => $this->allocateId(),
            'subscription_id' => $subscriptionId,
            'old_amount' => $oldAmount,
            'new_amount' => $newAmount,
            'currency' => strtoupper($currency),
            'change_amount' => $changeAmount,
            'change_percent' => $changePercent,
            'detected_via' => $detectedVia,
            'recorded_at' => now()->toDateTimeString(),
        ];

        session([self::KEY_PRICE_HISTORIES => $histories]);
    }

    public function recordBillingEvent(
        int $userId,
        int $subscriptionId,
        float $amount,
        string $currency,
        string $billingCycle,
        string $source = self::SOURCE_RECURRING
    ): void {
        $this->ensureInitialized($userId);
        $events = (array) session(self::KEY_BILLING_EVENTS, []);
        $baseCurrency = session(self::KEY_BASE_CURRENCY, self::DEFAULT_CURRENCY);
        $amountBase = $this->fx->convertForSandbox($amount, $currency, $baseCurrency);
        $fxRate = $amount > 0 ? round($amountBase / $amount, 6) : 1.0;

        $events[] = [
            'id' => $this->allocateId(),
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'fx_rate_snapshot' => $fxRate,
            'amount_base' => $amountBase,
            'base_currency' => $baseCurrency,
            'billing_cycle' => $billingCycle,
            'billed_at' => now()->toDateString(),
            'source' => $source,
        ];

        session([self::KEY_BILLING_EVENTS => $events]);
    }

    public function hydrateRelations(Subscription $subscription): void
    {
        $subId = (int) $subscription->id;
        $userId = (int) $subscription->user_id;

        $histories = $this->getPriceHistories($userId, $subId);
        $events = $this->getBillingEvents($userId, $subId);

        $subscription->setRelation('priceHistories', $histories);
        $subscription->setRelation('billingEvents', $events);
    }
}
