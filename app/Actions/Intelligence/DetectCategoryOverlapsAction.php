<?php

declare(strict_types=1);

namespace App\Actions\Intelligence;

use App\Models\Subscription;
use Illuminate\Support\Collection;

class DetectCategoryOverlapsAction
{
    /**
     * @param  Collection<int, Subscription>  $activeSubscriptions
     * @return Collection<int, array{category_id: int|null, category_name: string, count: int, names: array<int, string>, monthly_total: float, potential_yearly_savings: float}>
     */
    public function execute(Collection $activeSubscriptions, string $baseCurrency): Collection
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
                    'category_name' => $category !== null ? $category->name : 'Category',
                    'count' => $group->count(),
                    'names' => $group->pluck('name')->map(fn (mixed $name): string => (string) $name)->values()->all(),
                    'monthly_total' => $monthlyTotal,
                    'potential_yearly_savings' => $lowestYearly,
                ];
            })
            ->sortByDesc('potential_yearly_savings')
            ->values();
    }
}
