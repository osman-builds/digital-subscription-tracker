<?php

declare(strict_types=1);

namespace App\Actions\Intelligence;

use App\Models\Subscription;
use Illuminate\Support\Collection;

class ComputeHealthScoreAction
{
    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @param  array<string, mixed>  $budget
     * @param  Collection<int, array{subscription: Subscription, days_left: int|null, monthly_cost: float}>  $expiringTrials
     * @param  array<string, mixed>  $priceCreep
     * @param  Collection<int, array{category_id: int|null, category_name: string, count: int, names: array<int, string>, monthly_total: float, potential_yearly_savings: float}>  $overlaps
     * @return array{score: int, grade: string, factors: array<int, array<string, mixed>>}
     */
    public function execute(
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
            $factors[] = ['label' => 'Monthly budget exceeded', 'impact' => -25, 'type' => 'critical'];
        } elseif ($budget['status'] === 'warning') {
            $score -= 10;
            $factors[] = ['label' => 'Approaching monthly budget cap', 'impact' => -10, 'type' => 'warning'];
        }

        if ($expiringTrials->isNotEmpty()) {
            $deduction = min(20, $expiringTrials->count() * 10);
            $score -= $deduction;
            $factors[] = ['label' => $expiringTrials->count().' trial(s) expiring within 7 days', 'impact' => -$deduction, 'type' => 'warning'];
        }

        $creepCount = $priceCreep['items']->count();
        if ($creepCount > 0) {
            $deduction = min(20, $creepCount * 10);
            $score -= $deduction;
            $factors[] = ['label' => "{$creepCount} subscription price hike(s) detected", 'impact' => -$deduction, 'type' => 'warning'];
        }

        if ($overlaps->isNotEmpty()) {
            $deduction = min(16, $overlaps->count() * 8);
            $score -= $deduction;
            $factors[] = ['label' => $overlaps->count().' overlapping service category/categories', 'impact' => -$deduction, 'type' => 'info'];
        }

        $uncategorized = $activeSubscriptions->whereNull('category_id')->count();
        if ($uncategorized >= 2) {
            $score -= 5;
            $factors[] = ['label' => "{$uncategorized} uncategorised subscriptions", 'impact' => -5, 'type' => 'info'];
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
}
