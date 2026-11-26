<?php

declare(strict_types=1);

namespace App\Actions\Intelligence;

use App\Models\InsightAlert;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Collection;

class SyncInsightAlertsAction
{
    private const SEVERITY_CRITICAL = 'critical';

    private const SEVERITY_WARNING = 'warning';

    private const SEVERITY_INFO = 'info';

    /**
     * @param  array{code: string, symbol: string}  $currency
     * @param  array<string, mixed>  $budget
     * @param  Collection<int, array{subscription: Subscription, days_left: int|null, monthly_cost: float}>  $expiringTrials
     * @param  Collection<int, array{month_key: string, month_label: string, projected_amount: float, has_lumpy_renewal: bool, events: array<int, array{name: string, cycle: string, amount_base: float}>, ratio_to_average: float}>  $renewalSpikes
     * @param  Collection<int, array{category_id: int|null, category_name: string, count: int, names: array<int, string>, monthly_total: float, potential_yearly_savings: float}>  $overlaps
     * @return Collection<int, InsightAlert>
     */
    public function execute(
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
            $this->syncAlertsToDatabase((int) $user->id, $candidates);
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
     * @param  Collection<int, array{subscription: Subscription, days_left: int|null, monthly_cost: float}>  $expiringTrials
     * @param  Collection<int, array{month_key: string, month_label: string, projected_amount: float, has_lumpy_renewal: bool, events: array<int, array{name: string, cycle: string, amount_base: float}>, ratio_to_average: float}>  $renewalSpikes
     * @param  Collection<int, array{category_id: int|null, category_name: string, count: int, names: array<int, string>, monthly_total: float, potential_yearly_savings: float}>  $overlaps
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
        $existingByKey = InsightAlert::where('user_id', $userId)
            ->whereIn('dedupe_key', $keys)
            ->get()
            ->keyBy('dedupe_key');

        foreach ($candidates as $spec) {
            $existing = $existingByKey->get($spec['dedupe_key']);
            if ($existing !== null && $existing->dismissed_at !== null) {
                continue;
            }

            $this->upsertAlertIfChanged($userId, $spec, $existing);
        }
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function upsertAlertIfChanged(int $userId, array $spec, ?InsightAlert $existing): void
    {
        $attributes = [
            'subscription_id' => $spec['subscription_id'],
            'alert_type' => $spec['alert_type'],
            'severity' => $spec['severity'],
            'title' => $spec['title'],
            'message' => $spec['message'],
        ];

        if ($existing === null) {
            InsightAlert::create(array_merge($attributes, [
                'user_id' => $userId,
                'dedupe_key' => $spec['dedupe_key'],
            ]));

            return;
        }

        $existing->fill($attributes);
        if ($existing->isDirty()) {
            $existing->save();
        }
    }
}
