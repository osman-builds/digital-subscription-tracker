<?php

declare(strict_types=1);

namespace App\Actions\Intelligence;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ForecastRenewalSpikesAction
{
    /**
     * Project exact 12-month cashflow spikes caused by annual, semi-annual, and quarterly renewals.
     *
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return Collection<int, array{month_key: string, month_label: string, projected_amount: float, has_lumpy_renewal: bool, events: array<int, array{name: string, cycle: string, amount_base: float}>, ratio_to_average: float}>
     */
    public function execute(Collection $activeSubscriptions, string $baseCurrency): Collection
    {
        if ($activeSubscriptions->isEmpty()) {
            return collect();
        }

        $horizonStart = now()->startOfMonth();
        $horizonEnd = $horizonStart->copy()->addMonths(12)->subDay();
        /** @var array<string, array{month_key: string, month_label: string, projected_amount: float, has_lumpy_renewal: bool, events: array<int, array{name: string, cycle: string, amount_base: float}>}> $buckets */
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
                $projected = round($bucket['projected_amount'], 2);

                return [
                    'month_key' => $bucket['month_key'],
                    'month_label' => $bucket['month_label'],
                    'projected_amount' => $projected,
                    'has_lumpy_renewal' => $bucket['has_lumpy_renewal'],
                    'events' => $bucket['events'],
                    'ratio_to_average' => round($projected / $avgMonthly, 1),
                ];
            })
            ->sortByDesc('projected_amount')
            ->values();
    }

    /**
     * @param  array<string, array{month_key: string, month_label: string, projected_amount: float, has_lumpy_renewal: bool, events: array<int, array{name: string, cycle: string, amount_base: float}>}>  $buckets
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
}
