<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\FxRateService;

class DashboardController extends Controller
{
    public function index(FxRateService $fx)
    {
        $userId = auth()->id();

        $subscriptions = Subscription::with('category')
            ->forUser($userId)
            ->active()
            ->get();

        $totalMonthlyMyr = $subscriptions->sum(fn ($s) => $s->monthlyAmountMyr());
        $totalYearlyMyr  = $subscriptions->sum(fn ($s) => $s->yearlyAmountMyr());
        $totalActive     = $subscriptions->count();

        $dueSoon = Subscription::with('category')
            ->forUser($userId)
            ->dueSoon(7)
            ->orderBy('next_billing_date')
            ->get();

        // Category breakdown for chart
        $categoryBreakdown = $subscriptions
            ->groupBy(fn ($s) => $s->category->name ?? 'Uncategorised')
            ->map(fn ($group) => round($group->sum(fn ($s) => $s->monthlyAmountMyr()), 2))
            ->sortDesc();

        $fxUpdatedAt = $fx->lastUpdated();

        return view('dashboard', compact(
            'totalMonthlyMyr', 'totalYearlyMyr', 'totalActive',
            'dueSoon', 'subscriptions', 'categoryBreakdown', 'fxUpdatedAt'
        ));
    }
}
