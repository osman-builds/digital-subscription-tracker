<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\UserSetting;
use Illuminate\Support\Collection;

/**
 * Ephemeral in-session sandbox store for demo testers.
 * Every tester sees an isolated environment whose changes stay solely in their
 * HTTP session and auto-expire after the configured TTL without DB writes.
 */
class SandboxService
{
    private const SESSION_KEY = 'sandbox';

    private const VIRTUAL_ID_START = 100001;

    public function __construct(private FxRateService $fx) {}

    public function startSession(int $userId): void
    {
        $ttlMinutes = max(1, (int) config('demo.sandbox_ttl_minutes', 60));

        session([
            self::SESSION_KEY => [
                'active' => true,
                'user_id' => $userId,
                'expires_at' => now()->addMinutes($ttlMinutes)->timestamp,
                'next_id' => self::VIRTUAL_ID_START,
                'categories' => [],
                'subscriptions' => $this->buildSampleSubscriptions($userId),
                'settings' => [
                    'user_id' => $userId,
                    'base_currency' => 'MYR',
                    'alert_7_days' => false,
                    'alert_3_days' => false,
                    'dark_mode' => false,
                ],
            ],
        ]);
    }

    public function ensureInitialized(int $userId): void
    {
        $key = self::SESSION_KEY.'.expires_at';

        if (! session()->has($key) || (int) session(self::SESSION_KEY.'.user_id') !== $userId) {
            $this->startSession($userId);
        }
    }

    public function isExpired(): bool
    {
        $expiresAt = session(self::SESSION_KEY.'.expires_at');

        return $expiresAt !== null && now()->timestamp >= (int) $expiresAt;
    }

    public function remainingMinutes(): int
    {
        $expiresAt = (int) session(self::SESSION_KEY.'.expires_at', 0);

        return (int) max(0, ceil(($expiresAt - now()->timestamp) / 60));
    }

    public function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function getSettings(int $userId): UserSetting
    {
        $this->ensureInitialized($userId);
        $raw = (array) session(self::SESSION_KEY.'.settings', []);

        $setting = new UserSetting;
        $setting->forceFill([
            'user_id' => $userId,
            'base_currency' => $raw['base_currency'] ?? 'MYR',
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
            self::SESSION_KEY.'.settings.base_currency' => strtoupper($baseCurrency),
            self::SESSION_KEY.'.settings.alert_7_days' => false,
            self::SESSION_KEY.'.settings.alert_3_days' => false,
        ]);
    }

    public function updateDarkMode(int $userId, bool $darkMode): bool
    {
        $this->ensureInitialized($userId);
        session([self::SESSION_KEY.'.settings.dark_mode' => $darkMode]);

        return $darkMode;
    }

    /** @return Collection<int, Category> */
    public function allCategories(int $userId): Collection
    {
        $this->ensureInitialized($userId);

        $systemCategories = Category::whereNull('user_id')->orderBy('name')->get();
        $sessionItems = (array) session(self::SESSION_KEY.'.categories', []);
        $subscriptions = (array) session(self::SESSION_KEY.'.subscriptions', []);

        $counts = [];
        foreach ($subscriptions as $sub) {
            $catId = $sub['category_id'] ?? null;
            if ($catId !== null) {
                $counts[(int) $catId] = ($counts[(int) $catId] ?? 0) + 1;
            }
        }

        $customCategories = collect($sessionItems)
            ->sortBy('name')
            ->map(fn (array $item): Category => $this->hydrateCategory($item, $userId));

        return $systemCategories->concat($customCategories)->each(function (Category $cat) use ($counts): void {
            $cat->setAttribute('subscriptions_count', $counts[(int) $cat->id] ?? 0);
        })->values();
    }

    public function categoryNameExists(int $userId, string $name, ?int $ignoreId = null): bool
    {
        $normalized = strtolower(trim($name));

        return $this->allCategories($userId)->contains(function (Category $cat) use ($normalized, $ignoreId): bool {
            if ($ignoreId !== null && (int) $cat->id === $ignoreId) {
                return false;
            }

            return strtolower($cat->name) === $normalized;
        });
    }

    public function findCategoryOrFail(int $userId, int $categoryId): Category
    {
        $category = $this->allCategories($userId)->firstWhere('id', $categoryId);
        abort_if($category === null, 404);

        return $category;
    }

    public function createCategory(int $userId, array $attributes): Category
    {
        $this->ensureInitialized($userId);
        $nextId = $this->allocateId();

        $item = [
            'id' => $nextId,
            'user_id' => $userId,
            'name' => $attributes['name'],
            'color' => $attributes['color'],
        ];

        $categories = (array) session(self::SESSION_KEY.'.categories', []);
        $categories[$nextId] = $item;
        session([self::SESSION_KEY.'.categories' => $categories]);

        return $this->hydrateCategory($item, $userId);
    }

    public function updateCategory(int $userId, Category $category, array $attributes): void
    {
        $this->ensureInitialized($userId);
        $categories = (array) session(self::SESSION_KEY.'.categories', []);
        $id = (int) $category->id;

        abort_if(! isset($categories[$id]), 404);

        $categories[$id]['name'] = $attributes['name'];
        $categories[$id]['color'] = $attributes['color'];
        session([self::SESSION_KEY.'.categories' => $categories]);
    }

    public function deleteCategory(int $userId, Category $category): void
    {
        $this->ensureInitialized($userId);
        $id = (int) $category->id;

        $categories = (array) session(self::SESSION_KEY.'.categories', []);
        unset($categories[$id]);

        $subscriptions = (array) session(self::SESSION_KEY.'.subscriptions', []);
        foreach ($subscriptions as $subId => $sub) {
            if ((int) ($sub['category_id'] ?? 0) === $id) {
                $subscriptions[$subId]['category_id'] = null;
            }
        }

        session([
            self::SESSION_KEY.'.categories' => $categories,
            self::SESSION_KEY.'.subscriptions' => $subscriptions,
        ]);
    }

    /** @return Collection<int, Subscription> */
    public function allSubscriptions(int $userId): Collection
    {
        $this->ensureInitialized($userId);
        $categoriesById = $this->allCategories($userId)->keyBy('id');
        $items = (array) session(self::SESSION_KEY.'.subscriptions', []);

        return collect($items)
            ->map(fn (array $item): Subscription => $this->hydrateSubscription($item, $categoriesById))
            ->values();
    }

    /** @return Collection<int, Subscription> */
    public function activeSubscriptions(int $userId): Collection
    {
        return $this->allSubscriptions($userId)->where('status', 'active')->values();
    }

    /** @return Collection<int, Subscription> */
    public function dueSoonSubscriptions(int $userId, int $days = 7): Collection
    {
        $start = now()->startOfDay();
        $end = now()->addDays($days)->endOfDay();

        return $this->activeSubscriptions($userId)
            ->filter(fn (Subscription $sub): bool => $sub->next_billing_date->between($start, $end))
            ->sortBy('next_billing_date')
            ->values();
    }

    public function findSubscriptionOrFail(int $userId, int $subscriptionId): Subscription
    {
        $subscription = $this->allSubscriptions($userId)->firstWhere('id', $subscriptionId);
        abort_if($subscription === null, 404);

        return $subscription;
    }

    public function createSubscription(int $userId, array $attributes): Subscription
    {
        $this->ensureInitialized($userId);
        $nextId = $this->allocateId();

        $item = [
            'id' => $nextId,
            'user_id' => $userId,
            'category_id' => $attributes['category_id'] ?? null,
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'amount' => (float) $attributes['amount'],
            'currency' => strtoupper((string) $attributes['currency']),
            'amount_myr' => (float) $attributes['amount_myr'],
            'billing_cycle' => $attributes['billing_cycle'],
            'start_date' => $attributes['start_date'],
            'next_billing_date' => $attributes['next_billing_date'],
            'status' => $attributes['status'],
            'website_url' => $attributes['website_url'] ?? null,
            'logo_url' => $attributes['logo_url'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ];

        $subscriptions = (array) session(self::SESSION_KEY.'.subscriptions', []);
        $subscriptions[$nextId] = $item;
        session([self::SESSION_KEY.'.subscriptions' => $subscriptions]);

        return $this->findSubscriptionOrFail($userId, $nextId);
    }

    public function updateSubscription(int $userId, Subscription $subscription, array $attributes): void
    {
        $this->ensureInitialized($userId);
        $subscriptions = (array) session(self::SESSION_KEY.'.subscriptions', []);
        $id = (int) $subscription->id;

        abort_if(! isset($subscriptions[$id]), 404);

        $subscriptions[$id] = array_merge($subscriptions[$id], $attributes);
        session([self::SESSION_KEY.'.subscriptions' => $subscriptions]);
    }

    public function deleteSubscription(int $userId, Subscription $subscription): void
    {
        $this->ensureInitialized($userId);
        $subscriptions = (array) session(self::SESSION_KEY.'.subscriptions', []);
        unset($subscriptions[(int) $subscription->id]);
        session([self::SESSION_KEY.'.subscriptions' => $subscriptions]);
    }

    private function allocateId(): int
    {
        $nextId = (int) session(self::SESSION_KEY.'.next_id', self::VIRTUAL_ID_START);
        session([self::SESSION_KEY.'.next_id' => $nextId + 1]);

        return $nextId;
    }

    private function hydrateCategory(array $item, int $userId): Category
    {
        $category = new Category;
        $category->forceFill([
            'id' => (int) $item['id'],
            'user_id' => $userId,
            'name' => $item['name'],
            'color' => $item['color'],
        ]);

        return $category;
    }

    /** @param Collection<int|string, Category> $categoriesById */
    private function hydrateSubscription(array $item, Collection $categoriesById): Subscription
    {
        $subscription = new Subscription;
        $subscription->forceFill($item);

        $catId = $item['category_id'] ?? null;
        if ($catId !== null && $categoriesById->has((int) $catId)) {
            $subscription->setRelation('category', $categoriesById->get((int) $catId));
        }

        return $subscription;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildSampleSubscriptions(int $userId): array
    {
        $categoryMap = Category::whereNull('user_id')->pluck('id', 'name');
        $today = now();

        $templates = [
            [
                'name' => 'Netflix',
                'description' => 'Streaming movies and TV shows',
                'amount' => 15.49,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'start_date' => $today->copy()->subMonths(6)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(2)->toDateString(),
                'status' => 'active',
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
                'billing_cycle' => 'monthly',
                'start_date' => $today->copy()->subMonths(8)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(5)->toDateString(),
                'status' => 'active',
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
                'billing_cycle' => 'monthly',
                'start_date' => $today->copy()->subMonths(4)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(12)->toDateString(),
                'status' => 'active',
                'category_id' => $categoryMap['Software & Dev'] ?? null,
                'website_url' => 'https://github.com',
                'logo_url' => null,
                'notes' => null,
            ],
            [
                'name' => 'iCloud+',
                'description' => '2TB family cloud backup',
                'amount' => 44.90,
                'currency' => 'MYR',
                'billing_cycle' => 'monthly',
                'start_date' => $today->copy()->subMonths(10)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(18)->toDateString(),
                'status' => 'active',
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
                'status' => 'active',
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
            $tpl['amount_myr'] = $this->fx->convertForSandbox((float) $tpl['amount'], $tpl['currency'], 'MYR');
            $result[$id] = $tpl;
        }

        return $result;
    }
}
