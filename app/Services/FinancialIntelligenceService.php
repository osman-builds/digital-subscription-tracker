<?php

namespace App\Services;

use App\Actions\Intelligence\ComputeHealthScoreAction;
use App\Actions\Intelligence\DetectCategoryOverlapsAction;
use App\Actions\Intelligence\ForecastRenewalSpikesAction;
use App\Actions\Intelligence\SyncInsightAlertsAction;
use App\Models\Subscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Proactive Decision Support orchestrator: coordinates budget guardrails, trial shocks,
 * category overlaps, cashflow renewal spikes, price creep, and composite health score.
 */
class FinancialIntelligenceService
{
    private const DEFAULT_CURRENCY = 'MYR';

    public function __construct(
        private readonly FxRateService $fx,
        private readonly ComputeHealthScoreAction $computeHealthScore,
        private readonly ForecastRenewalSpikesAction $forecastRenewalSpikes,
        private readonly DetectCategoryOverlapsAction $detectCategoryOverlaps,
        private readonly SyncInsightAlertsAction $syncInsightAlerts,
    ) {}

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
        $renewalSpikes = $this->forecastRenewalSpikes->execute($activeSubscriptions, $baseCurrency);
        $categoryOverlaps = $this->detectCategoryOverlaps->execute($activeSubscriptions, $baseCurrency);
        $health = $this->computeHealthScore->execute(
            $activeSubscriptions,
            $budget,
            $expiringTrials,
            $priceCreep,
            $categoryOverlaps
        );

        $alerts = $this->syncInsightAlerts->execute(
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
                $status = 'warning';
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
     * @return Collection<int, array{subscription: Subscription, days_left: int|null, monthly_cost: float}>
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
            $annualImpact = round($deltaBase * $this->annualMultiplier($sub?->billing_cycle), 2);
            $extraYearly += $annualImpact;

            return [
                'subscription_id' => $hike->subscription_id,
                'name' => $sub !== null ? $sub->name : 'Subscription',
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
                $annualImpact = round($deltaBase * $this->annualMultiplier($sub->billing_cycle), 2);
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

    private function annualMultiplier(?string $cycle): int
    {
        return match ($cycle) {
            'weekly' => 52,
            'quarterly' => 4,
            'semi_annual' => 2,
            'annual' => 1,
            default => 12,
        };
    }
}
