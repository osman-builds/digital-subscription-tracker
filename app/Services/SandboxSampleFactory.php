<?php

namespace App\Services;

use App\Models\Category;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds ephemeral pre-loaded sample portfolio datasets for isolated sandbox sessions.
 */
class SandboxSampleFactory
{
    private const VIRTUAL_ID_START = 100001;

    private const DEFAULT_CURRENCY = 'MYR';

    private const CURRENCY_USD = 'USD';

    private const CURRENCY_EUR = 'EUR';

    private const CYCLE_MONTHLY = 'monthly';

    private const CYCLE_ANNUAL = 'annual';

    private const STATUS_ACTIVE = 'active';

    private const NETFLIX_NAME = 'Netflix';

    private const SPOTIFY_NAME = 'Spotify';

    private const GITHUB_COPILOT_NAME = 'GitHub Copilot';

    private const CAT_ENTERTAINMENT = 'Entertainment';

    private const CAT_PRODUCTIVITY = 'Productivity';

    private const CAT_MUSIC = 'Music';

    private const CAT_CLOUD = 'Cloud Storage';

    private const CAT_GAMING = 'Gaming';

    private const CAT_NEWS = 'News & Reading';

    private const CAT_HEALTH = 'Health & Fitness';

    private const CAT_SOFTWARE = 'Software & Dev';

    public function __construct(private readonly FxRateService $fx) {}

    /** @return array<int, array<string, mixed>> */
    public function buildSubscriptions(int $userId): array
    {
        $categoryMap = Category::whereNull('user_id')->pluck('id', 'name');
        if ($categoryMap->isEmpty()) {
            $categoryMap = collect([
                self::CAT_ENTERTAINMENT => 1,
                self::CAT_PRODUCTIVITY => 2,
                self::CAT_MUSIC => 3,
                self::CAT_CLOUD => 4,
                self::CAT_GAMING => 5,
                self::CAT_NEWS => 6,
                self::CAT_HEALTH => 7,
                self::CAT_SOFTWARE => 8,
            ]);
        }

        $today = now();
        $templates = array_merge(
            $this->buildCoreTemplates($categoryMap, $today),
            $this->buildExtendedTemplates($categoryMap, $today)
        );

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

    /**
     * @param  array<int, array<string, mixed>>  $sampleSubs
     * @return array<int, array<string, mixed>>
     */
    public function buildPriceHistories(array $sampleSubs): array
    {
        $histories = [];
        $subsByName = collect($sampleSubs)->keyBy('name');

        if ($subsByName->has(self::NETFLIX_NAME)) {
            $netflix = $subsByName->get(self::NETFLIX_NAME);
            $histories[] = [
                'id' => self::VIRTUAL_ID_START - 200,
                'subscription_id' => $netflix['id'],
                'old_amount' => 13.99,
                'new_amount' => 15.49,
                'currency' => self::CURRENCY_USD,
                'change_amount' => 1.50,
                'change_percent' => 10.7,
                'detected_via' => SandboxService::SOURCE_AI_SCAN,
                'recorded_at' => now()->subDays(28)->toDateTimeString(),
            ];
        }

        return $histories;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sampleSubs
     * @return array<int, array<string, mixed>>
     */
    public function buildBillingEvents(array $sampleSubs, int $userId): array
    {
        $events = [];
        $subsByName = collect($sampleSubs)->keyBy('name');
        $idCounter = self::VIRTUAL_ID_START - 100;
        $today = now();

        $specs = [
            ['name' => self::NETFLIX_NAME, 'amount' => 15.49, 'currency' => self::CURRENCY_USD, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 28, 'source' => SandboxService::SOURCE_AI_SCAN, 'fx' => 4.4500],
            ['name' => self::SPOTIFY_NAME, 'amount' => 10.99, 'currency' => self::CURRENCY_EUR, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 25, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 4.7000],
            ['name' => self::GITHUB_COPILOT_NAME, 'amount' => 10.00, 'currency' => self::CURRENCY_USD, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 29, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 4.4500],
            ['name' => 'iCloud+', 'amount' => 44.90, 'currency' => self::DEFAULT_CURRENCY, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 9, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 1.0000],
            ['name' => 'Google One', 'amount' => 42.99, 'currency' => self::DEFAULT_CURRENCY, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 18, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 1.0000],
            ['name' => 'Disney+', 'amount' => 39.90, 'currency' => self::DEFAULT_CURRENCY, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 14, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 1.0000],
            ['name' => 'Strava Summit', 'amount' => 24.90, 'currency' => self::DEFAULT_CURRENCY, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 12, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 1.0000],
            ['name' => self::NETFLIX_NAME, 'amount' => 13.99, 'currency' => self::CURRENCY_USD, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 58, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 4.4200],
            ['name' => self::SPOTIFY_NAME, 'amount' => 9.99, 'currency' => self::CURRENCY_EUR, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 55, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 4.6800],
            ['name' => self::GITHUB_COPILOT_NAME, 'amount' => 10.00, 'currency' => self::CURRENCY_USD, 'cycle' => self::CYCLE_MONTHLY, 'days_ago' => 59, 'source' => SandboxService::SOURCE_RECURRING, 'fx' => 4.4200],
        ];

        foreach ($specs as $spec) {
            $sub = $subsByName->get($spec['name']);
            if ($sub === null) {
                continue;
            }

            $idCounter++;
            $amountBase = round($spec['amount'] * $spec['fx'], 2);
            $events[] = [
                'id' => $idCounter,
                'user_id' => $userId,
                'subscription_id' => $sub['id'],
                'amount' => $spec['amount'],
                'currency' => $spec['currency'],
                'fx_rate_snapshot' => $spec['fx'],
                'amount_base' => $amountBase,
                'base_currency' => self::DEFAULT_CURRENCY,
                'billing_cycle' => $spec['cycle'],
                'billed_at' => $today->copy()->subDays($spec['days_ago'])->toDateString(),
                'source' => $spec['source'],
            ];
        }

        return $events;
    }

    /**
     * @param  Collection<string, int>  $categoryMap
     * @return array<int, array<string, mixed>>
     */
    private function buildCoreTemplates(Collection $categoryMap, CarbonInterface $today): array
    {
        return [
            [
                'name' => self::NETFLIX_NAME,
                'description' => '4K Ultra HD family streaming plan',
                'amount' => 15.49,
                'currency' => self::CURRENCY_USD,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(6)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(2)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_ENTERTAINMENT] ?? null,
                'website_url' => 'https://netflix.com',
                'logo_url' => null,
                'notes' => 'Recent price hike detected via receipt scan',
            ],
            [
                'name' => 'Disney+',
                'description' => 'Streaming movies and TV shows',
                'amount' => 39.90,
                'currency' => self::DEFAULT_CURRENCY,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(4)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(16)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_ENTERTAINMENT] ?? null,
                'website_url' => 'https://disneyplus.com',
                'logo_url' => null,
                'notes' => 'Redundant with Netflix in Entertainment',
            ],
            [
                'name' => self::SPOTIFY_NAME,
                'description' => 'Premium EU music streaming plan',
                'amount' => 10.99,
                'currency' => self::CURRENCY_EUR,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(8)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(5)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_MUSIC] ?? null,
                'website_url' => 'https://spotify.com',
                'logo_url' => null,
                'notes' => null,
            ],
            [
                'name' => self::GITHUB_COPILOT_NAME,
                'description' => 'AI code completion for developers',
                'amount' => 10.00,
                'currency' => self::CURRENCY_USD,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(5)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(1)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_SOFTWARE] ?? null,
                'website_url' => 'https://github.com',
                'logo_url' => null,
                'notes' => 'Renewing tomorrow',
            ],
            [
                'name' => 'iCloud+',
                'description' => '2TB family cloud backup and storage',
                'amount' => 44.90,
                'currency' => self::DEFAULT_CURRENCY,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(10)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(21)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_CLOUD] ?? null,
                'website_url' => 'https://icloud.com',
                'logo_url' => null,
                'notes' => null,
            ],
        ];
    }

    /**
     * @param  Collection<string, int>  $categoryMap
     * @return array<int, array<string, mixed>>
     */
    private function buildExtendedTemplates(Collection $categoryMap, CarbonInterface $today): array
    {
        return [
            [
                'name' => 'Google One',
                'description' => '2TB cloud storage with Gemini Advanced',
                'amount' => 42.99,
                'currency' => self::DEFAULT_CURRENCY,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(3)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(12)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_CLOUD] ?? null,
                'website_url' => 'https://one.google.com',
                'logo_url' => null,
                'notes' => 'Redundant cloud storage overlap with iCloud+',
            ],
            [
                'name' => 'ChatGPT Plus',
                'description' => 'Advanced AI reasoning & image generation',
                'amount' => 20.00,
                'currency' => self::CURRENCY_USD,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subDays(11)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(3)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => true,
                'trial_ends_at' => $today->copy()->addDays(3)->toDateString(),
                'category_id' => $categoryMap[self::CAT_PRODUCTIVITY] ?? null,
                'website_url' => 'https://chatgpt.com',
                'logo_url' => null,
                'notes' => '14-day free trial ending in 3 days — auto-renews at $20/mo',
            ],
            [
                'name' => 'Notion Plus',
                'description' => 'Team workspace docs, wikis, and projects',
                'amount' => 96.00,
                'currency' => self::CURRENCY_USD,
                'billing_cycle' => self::CYCLE_ANNUAL,
                'start_date' => $today->copy()->subMonths(8)->toDateString(),
                'next_billing_date' => $today->copy()->addMonths(4)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_PRODUCTIVITY] ?? null,
                'website_url' => 'https://notion.so',
                'logo_url' => null,
                'notes' => 'Annual workspace plan',
            ],
            [
                'name' => 'JetBrains All Products',
                'description' => 'Professional IDE developer toolbox',
                'amount' => 289.00,
                'currency' => self::CURRENCY_USD,
                'billing_cycle' => self::CYCLE_ANNUAL,
                'start_date' => $today->copy()->subMonths(9)->toDateString(),
                'next_billing_date' => $today->copy()->addMonths(3)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_SOFTWARE] ?? null,
                'website_url' => 'https://jetbrains.com',
                'logo_url' => null,
                'notes' => 'Annual IDE renewal spike forecasted in 3 months',
            ],
            [
                'name' => 'Strava Summit',
                'description' => 'GPS activity tracking and fitness metrics',
                'amount' => 24.90,
                'currency' => self::DEFAULT_CURRENCY,
                'billing_cycle' => self::CYCLE_MONTHLY,
                'start_date' => $today->copy()->subMonths(2)->toDateString(),
                'next_billing_date' => $today->copy()->addDays(18)->toDateString(),
                'status' => self::STATUS_ACTIVE,
                'is_trial' => false,
                'trial_ends_at' => null,
                'category_id' => $categoryMap[self::CAT_HEALTH] ?? null,
                'website_url' => 'https://strava.com',
                'logo_url' => null,
                'notes' => null,
            ],
        ];
    }
}
