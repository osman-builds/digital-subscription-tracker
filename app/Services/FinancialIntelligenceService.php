<?php

namespace App\Services;

use App\Models\InsightAlert;
use App\Models\Subscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Proactive Decision Support engine: analyzes budget guardrails, trial shocks,
 * category overlaps, cashflow renewal spikes, price creep, and composite health score.
 */
class FinancialIntelligenceService
{
    private const SEVERITY_CRITICAL = 'critical';

    private const SEVERITY_WARNING = 'warning';

    private const SEVERITY_INFO = 'info';

    private const DEFAULT_CURRENCY = 'MYR';

    public function __construct(private readonly FxRateService $fx) {}

    /**
     * Compute full proactive intelligence summary for dashboard & expenditure views.
     *
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        Collection $activeSubscriptions,
        UserSetting $settings,
        bool $syncAlerts = true
    ): array {
        $baseCurrency = strtoupper((string) ($settings->base_currency ?? self::DEFAULT_CURRENCY));
        $symbol = $this->fx->symbol($baseCurrency);

        $budget = $this->computeBudgetSummary($activeSubscriptions, $settings, $baseCurrency);
        $expiringTrials = $this->collectExpiringTrials($activeSubscriptions, $baseCurrency);
        $priceCreep = $this->collectPriceCreep($user, $activeSubscriptions, $baseCurrency);
        $renewalSpikes = $this->forecastRenewalSpikes($activeSubscriptions, $baseCurrency);
        $categoryOverlaps = $this->detectCategoryOverlaps($activeSubscriptions, $baseCurrency);
        $health = $this->computeHealthScore(
            $activeSubscriptions,
            $budget,
            $expiringTrials,
            $priceCreep,
            $categoryOverlaps
        );

        $alerts = $this->resolveAlerts(
            $user,
            ['code' => $baseCurrency, 'symbol' => $symbol],
            $budget,
            $expiringTrials,
            $renewalSpikes,
            $categoryOverlaps,
            $syncAlerts
        );

        return [
            'base_currency' => $baseCurrency,
            'symbol' => $symbol,
            'budget' => $budget,
            'expiring_trials' => $expiringTrials,
            'price_creep' => $priceCreep,
            'renewal_spikes' => $renewalSpikes,
            'category_overlaps' => $categoryOverlaps,
            'health_score' => $health['score'],
            'health_grade' => $health['grade'],
            'score_factors' => $health['factors'],
            'alerts' => $alerts,
        ];
    }

    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return array<string, mixed>
     */
    private function computeBudgetSummary(
        Collection $activeSubscriptions,
        UserSetting $settings,
        string $baseCurrency
    ): array {
        $currentMonthly = round(
            (float) $activeSubscriptions->sum(fn (Subscription $s): float => $s->monthlyAmountIn($baseCurrency)),
            2
        );
        $trialMonthly = round(
            (float) $activeSubscriptions->where('is_trial', true)->sum(fn (Subscription $s): float => $s->monthlyAmountIn($baseCurrency)),
            2
        );
        $paidMonthly = round(max(0.0, $currentMonthly - $trialMonthly), 2);

        $rawBudget = $settings->monthly_budget !== null ? (float) $settings->monthly_budget : null;
        $monthlyBudget = ($rawBudget !== null && $rawBudget > 0) ? round($rawBudget, 2) : null;
        $alertPercent = max(50, min(100, (int) ($settings->budget_alert_percent ?? 80)));

        $utilization = $monthlyBudget !== null ? round(($currentMonthly / $monthlyBudget) * 100, 1) : null;
        $status = 'unset';
        if ($monthlyBudget !== null) {
            if ($currentMonthly > $monthlyBudget) {
                $status = 'exceeded';
            } elseif ($utilization >= $alertPercent) {
                $status = self::SEVERITY_WARNING;
            } else {
                $status = 'healthy';
            }
        }

        $willExceedAfterTrials = $monthlyBudget !== null
            && $trialMonthly > 0
            && ($paidMonthly + $trialMonthly) > $monthlyBudget;

        return [
            'monthly_budget' => $monthlyBudget,
            'alert_percent' => $alertPercent,
            'current_monthly' => $currentMonthly,
            'paid_monthly' => $paidMonthly,
            'trial_conversion_monthly' => $trialMonthly,
            'utilization_percent' => $utilization,
            'status' => $status,
            'will_exceed_after_trials' => $willExceedAfterTrials,
            'remaining' => $monthlyBudget !== null ? round($monthlyBudget - $currentMonthly, 2) : null,
        ];
    }

    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return Collection<int, array<string, mixed>>
     */
    private function collectExpiringTrials(Collection $activeSubscriptions, string $baseCurrency): Collection
    {
        return $activeSubscriptions
            ->filter(function (Subscription $sub): bool {
                if (! $sub->is_trial) {
                    return false;
                }

                $daysLeft = $sub->daysUntilTrialEnds();

                return $daysLeft === null || $daysLeft <= 7;
            })
            ->map(function (Subscription $sub) use ($baseCurrency): array {
                return [
                    'subscription' => $sub,
                    'days_left' => $sub->daysUntilTrialEnds(),
                    'monthly_cost' => round($sub->monthlyAmountIn($baseCurrency), 2),
                ];
            })
            ->sortBy(fn (array $item): int => $item['days_left'] ?? 7)
            ->values();
    }

    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return array<string, mixed>
     */
    private function collectPriceCreep(
        User $user,
        Collection $activeSubscriptions,
        string $baseCurrency
    ): array {
        if ($activeSubscriptions->isEmpty()) {
            return ['items' => collect(), 'extra_yearly_cost' => 0.0];
        }

        if ($user->isSandbox()) {
            return $this->collectSandboxPriceCreep($activeSubscriptions, $baseCurrency);
        }

        $subIds = $activeSubscriptions->pluck('id')->all();
        $hikes = SubscriptionPriceHistory::whereIn('subscription_id', $subIds)
            ->where('change_amount', '>', 0)
            ->with('subscription')
            ->latest('recorded_at')
            ->get()
            ->unique('subscription_id')
            ->values();

        $extraYearly = 0.0;
        $items = $hikes->map(function (SubscriptionPriceHistory $hike) use ($baseCurrency, &$extraYearly): array {
            $sub = $hike->subscription;
            $deltaBase = $this->fx->convert((float) $hike->change_amount, $hike->currency, $baseCurrency);
            $annualMultiplier = match ($sub?->billing_cycle) {
                'weekly' => 52,
                'quarterly' => 4,
                'semi_annual' => 2,
                'annual' => 1,
                default => 12,
            };
            $annualImpact = round($deltaBase * $annualMultiplier, 2);
            $extraYearly += $annualImpact;

            return [
                'subscription_id' => $hike->subscription_id,
                'name' => $sub?->name ?? 'Subscription',
                'old_amount' => (float) $hike->old_amount,
                'new_amount' => (float) $hike->new_amount,
                'currency' => $hike->currency,
                'change_percent' => (float) $hike->change_percent,
                'annual_impact_base' => $annualImpact,
                'detected_via' => $hike->detected_via,
                'recorded_at' => $hike->recorded_at,
            ];
        });

        return [
            'items' => $items,
            'extra_yearly_cost' => round($extraYearly, 2),
        ];
    }

    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return array<string, mixed>
     */
    private function collectSandboxPriceCreep(
        Collection $activeSubscriptions,
        string $baseCurrency
    ): array {
        $raw = (array) session(SandboxService::KEY_PRICE_HISTORIES, []);
        if (empty($raw)) {
            return ['items' => collect(), 'extra_yearly_cost' => 0.0];
        }

        $extraYearly = 0.0;
        $subsById = $activeSubscriptions->keyBy('id');

        $items = collect($raw)
            ->where('change_amount', '>', 0)
            ->unique('subscription_id')
            ->map(function (array $hike) use ($subsById, $baseCurrency, &$extraYearly): ?array {
                $subId = (int) $hike['subscription_id'];
                $sub = $subsById->get($subId);
                if ($sub === null) {
                    return null;
                }

                $deltaBase = $this->fx->convert((float) $hike['change_amount'], (string) $hike['currency'], $baseCurrency);
                $annualMultiplier = match ($sub->billing_cycle) {
                    'weekly' => 52,
                    'quarterly' => 4,
                    'semi_annual' => 2,
                    'annual' => 1,
                    default => 12,
                };
                $annualImpact = round($deltaBase * $annualMultiplier, 2);
                $extraYearly += $annualImpact;

                return [
                    'subscription_id' => $subId,
                    'name' => $sub->name,
                    'old_amount' => (float) $hike['old_amount'],
                    'new_amount' => (float) $hike['new_amount'],
                    'currency' => (string) $hike['currency'],
                    'change_percent' => (float) $hike['change_percent'],
                    'annual_impact_base' => $annualImpact,
                    'detected_via' => (string) ($hike['detected_via'] ?? SandboxService::SOURCE_AI_SCAN),
                    'recorded_at' => isset($hike['recorded_at']) ? Carbon::parse($hike['recorded_at']) : now(),
                ];
            })
            ->filter()
            ->values();

        return [
            'items' => $items,
            'extra_yearly_cost' => round($extraYearly, 2),
        ];
    }

    /**
     * Project exact 12-month cashflow spikes caused by annual, semi-annual, and quarterly renewals.
     *
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return Collection<int, array<string, mixed>>
     */
    private function forecastRenewalSpikes(Collection $activeSubscriptions, string $baseCurrency): Collection
    {
        if ($activeSubscriptions->isEmpty()) {
            return collect();
        }

        $horizonStart = now()->startOfMonth();
        $horizonEnd = $horizonStart->copy()->addMonths(12)->subDay();
        $buckets = [];

        for ($i = 0; $i < 12; $i++) {
            $monthDate = $horizonStart->copy()->addMonths($i);
            $key = $monthDate->format('Y-m');
            $buckets[$key] = [
                'month_key' => $key,
                'month_label' => $monthDate->format('M Y'),
                'projected_amount' => 0.0,
                'has_lumpy_renewal' => false,
                'events' => [],
            ];
        }

        foreach ($activeSubscriptions as $sub) {
            $this->accumulateSubscriptionEvents($sub, $baseCurrency, $horizonStart, $horizonEnd, $buckets);
        }

        $bucketCollection = collect(array_values($buckets));
        $avgMonthly = (float) $bucketCollection->avg('projected_amount');

        if ($avgMonthly <= 0) {
            return collect();
        }

        return $bucketCollection
            ->filter(fn (array $bucket): bool => $bucket['has_lumpy_renewal']
                && $bucket['projected_amount'] >= ($avgMonthly * 1.5))
            ->map(function (array $bucket) use ($avgMonthly): array {
                $bucket['projected_amount'] = round($bucket['projected_amount'], 2);
                $bucket['ratio_to_average'] = round($bucket['projected_amount'] / $avgMonthly, 1);

                return $bucket;
            })
            ->sortByDesc('projected_amount')
            ->values();
    }

    /**
     * @param  array<string, array<string, mixed>>  $buckets
     */
    private function accumulateSubscriptionEvents(
        Subscription $sub,
        string $baseCurrency,
        Carbon $horizonStart,
        Carbon $horizonEnd,
        array &$buckets
    ): void {
        $due = $sub->next_billing_date?->copy()->startOfDay();
        if ($due === null) {
            return;
        }

        $guard = 0;
        while ($due->lt($horizonStart) && $guard < 60) {
            $due = $this->stepDateByCycle($due, (string) $sub->billing_cycle);
            $guard++;
        }

        $costBase = round($sub->effectiveAmountIn($baseCurrency), 2);
        $isLumpy = in_array($sub->billing_cycle, ['annual', 'semi_annual', 'quarterly'], true);

        $stepGuard = 0;
        while ($due->lte($horizonEnd) && $stepGuard < 60) {
            $key = $due->format('Y-m');
            if (isset($buckets[$key])) {
                $buckets[$key]['projected_amount'] += $costBase;
                if ($isLumpy) {
                    $buckets[$key]['has_lumpy_renewal'] = true;
                    $buckets[$key]['events'][] = [
                        'name' => $sub->name,
                        'cycle' => $sub->billingCycleLabel(),
                        'amount_base' => $costBase,
                    ];
                }
            }
            $due = $this->stepDateByCycle($due, (string) $sub->billing_cycle);
            $stepGuard++;
        }
    }

    private function stepDateByCycle(Carbon $date, string $cycle): Carbon
    {
        return match ($cycle) {
            'weekly' => $date->copy()->addWeek(),
            'quarterly' => $date->copy()->addMonths(3),
            'semi_annual' => $date->copy()->addMonths(6),
            'annual' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }

    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return Collection<int, array<string, mixed>>
     */
    private function detectCategoryOverlaps(Collection $activeSubscriptions, string $baseCurrency): Collection
    {
        return $activeSubscriptions
            ->filter(fn (Subscription $sub): bool => $sub->category_id !== null)
            ->groupBy('category_id')
            ->filter(fn (Collection $group): bool => $group->count() >= 2)
            ->map(function (Collection $group) use ($baseCurrency): array {
                $category = $group->first()->category;
                $monthlyTotal = round(
                    (float) $group->sum(fn (Subscription $s): float => $s->monthlyAmountIn($baseCurrency)),
                    2
                );
                $lowestYearly = round(
                    (float) $group->min(fn (Subscription $s): float => $s->yearlyAmountIn($baseCurrency)),
                    2
                );

                return [
                    'category_id' => $category?->id,
                    'category_name' => $category?->name ?? 'Category',
                    'count' => $group->count(),
                    'names' => $group->pluck('name')->values()->all(),
                    'monthly_total' => $monthlyTotal,
                    'potential_yearly_savings' => $lowestYearly,
                ];
            })
            ->sortByDesc('potential_yearly_savings')
            ->values();
    }

    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @param  array<string, mixed>  $budget
     * @param  Collection<int, array<string, mixed>>  $expiringTrials
     * @param  array<string, mixed>  $priceCreep
     * @param  Collection<int, array<string, mixed>>  $overlaps
     * @return array{score: int, grade: string, factors: array<int, array<string, mixed>>}
     */
    private function computeHealthScore(
        Collection $activeSubscriptions,
        array $budget,
        Collection $expiringTrials,
        array $priceCreep,
        Collection $overlaps
    ): array {
        $score = 100;
        $factors = [];

        if ($budget['status'] === 'exceeded') {
            $score -= 25;
            $factors[] = ['label' => 'Monthly budget exceeded', 'impact' => -25, 'type' => self::SEVERITY_CRITICAL];
        } elseif ($budget['status'] === self::SEVERITY_WARNING) {
            $score -= 10;
            $factors[] = ['label' => 'Approaching monthly budget cap', 'impact' => -10, 'type' => self::SEVERITY_WARNING];
        }

        if ($expiringTrials->isNotEmpty()) {
            $deduction = min(20, $expiringTrials->count() * 10);
            $score -= $deduction;
            $factors[] = ['label' => $expiringTrials->count().' trial(s) expiring within 7 days', 'impact' => -$deduction, 'type' => self::SEVERITY_WARNING];
        }

        $creepCount = $priceCreep['items']->count();
        if ($creepCount > 0) {
            $deduction = min(20, $creepCount * 10);
            $score -= $deduction;
            $factors[] = ['label' => "{$creepCount} subscription price hike(s) detected", 'impact' => -$deduction, 'type' => self::SEVERITY_WARNING];
        }

        if ($overlaps->isNotEmpty()) {
            $deduction = min(16, $overlaps->count() * 8);
            $score -= $deduction;
            $factors[] = ['label' => $overlaps->count().' overlapping service category/categories', 'impact' => -$deduction, 'type' => self::SEVERITY_INFO];
        }

        $uncategorized = $activeSubscriptions->whereNull('category_id')->count();
        if ($uncategorized >= 2) {
            $score -= 5;
            $factors[] = ['label' => "{$uncategorized} uncategorised subscriptions", 'impact' => -5, 'type' => self::SEVERITY_INFO];
        }

        $clamped = max(0, min(100, $score));
        $grade = match (true) {
            $clamped >= 85 => 'Excellent',
            $clamped >= 70 => 'Good',
            $clamped >= 50 => 'Fair',
            default => 'Needs Attention',
        };

        return ['score' => $clamped, 'grade' => $grade, 'factors' => $factors];
    }

    /**
     * @param  array{code: string, symbol: string}  $currency
     * @param  array<string, mixed>  $budget
     * @param  Collection<int, array<string, mixed>>  $expiringTrials
     * @param  Collection<int, array<string, mixed>>  $renewalSpikes
     * @param  Collection<int, array<string, mixed>>  $overlaps
     * @return Collection<int, InsightAlert>
     */
    private function resolveAlerts(
        User $user,
        array $currency,
        array $budget,
        Collection $expiringTrials,
        Collection $renewalSpikes,
        Collection $overlaps,
        bool $syncAlerts
    ): Collection {
        $candidates = $this->buildAlertCandidates(
            $currency['code'],
            $currency['symbol'],
            $budget,
            $expiringTrials,
            $renewalSpikes,
            $overlaps
        );

        if ($user->isSandbox()) {
            return collect($candidates)->map(function (array $spec) use ($user): InsightAlert {
                $alert = new InsightAlert;
                $alert->forceFill(array_merge($spec, [
                    'user_id' => $user->id,
                    'is_read' => false,
                ]));

                return $alert;
            });
        }

        if ($syncAlerts) {
            $this->syncAlertsToDatabase($user->id, $candidates);
        }

        $severityOrder = [
            self::SEVERITY_CRITICAL => 1,
            self::SEVERITY_WARNING => 2,
            self::SEVERITY_INFO => 3,
        ];

        return InsightAlert::where('user_id', $user->id)
            ->active()
            ->latest('created_at')
            ->get()
            ->sortBy(fn (InsightAlert $alert): int => $severityOrder[$alert->severity] ?? 9)
            ->take(8)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $budget
     * @param  Collection<int, array<string, mixed>>  $expiringTrials
     * @param  Collection<int, array<string, mixed>>  $renewalSpikes
     * @param  Collection<int, array<string, mixed>>  $overlaps
     * @return array<int, array<string, mixed>>
     */
    private function buildAlertCandidates(
        string $baseCurrency,
        string $symbol,
        array $budget,
        Collection $expiringTrials,
        Collection $renewalSpikes,
        Collection $overlaps
    ): array {
        $monthTag = now()->format('Y-m');
        $candidates = [];

        if ($budget['status'] === 'exceeded') {
            $candidates[] = [
                'subscription_id' => null,
                'alert_type' => 'budget_cap',
                'severity' => self::SEVERITY_CRITICAL,
                'title' => "Monthly Budget Exceeded ({$budget['utilization_percent']}%)",
                'message' => "Active spend is {$symbol} ".number_format($budget['current_monthly'], 2)." {$baseCurrency}/mo against your {$symbol} ".number_format($budget['monthly_budget'], 2).' cap.',
                'dedupe_key' => "budget_cap:{$monthTag}:exceeded",
            ];
        } elseif ($budget['status'] === self::SEVERITY_WARNING) {
            $candidates[] = [
                'subscription_id' => null,
                'alert_type' => 'budget_cap',
                'severity' => self::SEVERITY_WARNING,
                'title' => "Approaching Monthly Budget ({$budget['utilization_percent']}%)",
                'message' => "Current spend of {$symbol} ".number_format($budget['current_monthly'], 2)." is within {$budget['alert_percent']}%+ of your monthly cap.",
                'dedupe_key' => "budget_cap:{$monthTag}:warning",
            ];
        }

        foreach ($expiringTrials as $trial) {
            /** @var Subscription $sub */
            $sub = $trial['subscription'];
            $daysText = $trial['days_left'] !== null ? "in {$trial['days_left']} day(s)" : 'soon';
            $shockNote = $budget['will_exceed_after_trials']
                ? ' Converting will push you over your monthly budget.'
                : '';

            $candidates[] = [
                'subscription_id' => $sub->id,
                'alert_type' => 'trial_shock',
                'severity' => self::SEVERITY_WARNING,
                'title' => "Trial Converting Soon: {$sub->name}",
                'message' => "Free trial ends {$daysText} and converts at {$symbol} ".number_format($trial['monthly_cost'], 2)."/mo.{$shockNote}",
                'dedupe_key' => "trial_shock:{$sub->id}:{$monthTag}",
            ];
        }

        if ($renewalSpikes->isNotEmpty()) {
            $topSpike = $renewalSpikes->first();
            $candidates[] = [
                'subscription_id' => null,
                'alert_type' => 'annual_spike',
                'severity' => self::SEVERITY_WARNING,
                'title' => "Cashflow Spike in {$topSpike['month_label']}",
                'message' => "Projected {$topSpike['month_label']} cashflow is {$symbol} ".number_format($topSpike['projected_amount'], 2)." ({$topSpike['ratio_to_average']}x your normal monthly burn) due to lump-sum renewals.",
                'dedupe_key' => "annual_spike:{$topSpike['month_key']}",
            ];
        }

        if ($overlaps->isNotEmpty()) {
            $topOverlap = $overlaps->first();
            $candidates[] = [
                'subscription_id' => null,
                'alert_type' => 'overlap',
                'severity' => self::SEVERITY_INFO,
                'title' => "Category Overlap: {$topOverlap['category_name']} ({$topOverlap['count']} active)",
                'message' => 'You run '.implode(', ', $topOverlap['names'])." concurrently. Consolidating could save ~{$symbol} ".number_format($topOverlap['potential_yearly_savings'], 2).'/year.',
                'dedupe_key' => "overlap:{$topOverlap['category_id']}:{$monthTag}",
            ];
        }

        return $candidates;
    }

    /**
     * Upsert computed alerts without resurrecting any alert the user explicitly dismissed.
     *
     * @param  array<int, array<string, mixed>>  $candidates
     */
    private function syncAlertsToDatabase(int $userId, array $candidates): void
    {
        if ($candidates === []) {
            return;
        }

        $keys = array_column($candidates, 'dedupe_key');
        $dismissedKeys = InsightAlert::where('user_id', $userId)
            ->whereIn('dedupe_key', $keys)
            ->whereNotNull('dismissed_at')
            ->pluck('dedupe_key')
            ->all();

        foreach ($candidates as $spec) {
            if (in_array($spec['dedupe_key'], $dismissedKeys, true)) {
                continue;
            }

            InsightAlert::updateOrCreate(
                [
                    'user_id' => $userId,
                    'dedupe_key' => $spec['dedupe_key'],
                ],
                [
                    'subscription_id' => $spec['subscription_id'],
                    'alert_type' => $spec['alert_type'],
                    'severity' => $spec['severity'],
                    'title' => $spec['title'],
                    'message' => $spec['message'],
                ]
            );
        }
    }
}
