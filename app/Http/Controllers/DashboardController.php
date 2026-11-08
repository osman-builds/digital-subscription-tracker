<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\FxRateService;

class DashboardController extends Controller
{
    public function index(FxRateService $fx)
    {
        $user = auth()->user();
        $userId = $user->id;
        $settings = $user->getSettings();
        $baseCurrency = $settings->base_currency ?? 'MYR';
        $currencySymbol = $fx->symbol($baseCurrency);

        $subscriptions = Subscription::with('category')
            ->forUser($userId)
            ->active()
            ->get();

        $totalMonthly = $subscriptions->sum(fn ($s) => $s->monthlyAmountIn($baseCurrency));
        $totalYearly = $subscriptions->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency));
        $totalActive = $subscriptions->count();

        $dueSoon = Subscription::with('category')
            ->forUser($userId)
            ->dueSoon(7)
            ->orderBy('next_billing_date')
            ->get();

        // Category breakdown for chart in user's base currency
        $categoryBreakdown = $subscriptions
            ->groupBy(fn ($s) => $s->category->name ?? 'Uncategorised')
            ->map(fn ($group) => round($group->sum(fn ($s) => $s->monthlyAmountIn($baseCurrency)), 2))
            ->sortDesc();

        $fxUpdatedAt = $fx->lastUpdated();

        $totalMonthlyMyr = $subscriptions->sum(fn ($s) => $s->monthlyAmountMyr());
        $totalYearlyMyr = $subscriptions->sum(fn ($s) => $s->yearlyAmountMyr());

        return view('dashboard', compact(
            'totalMonthly', 'totalYearly', 'totalActive', 'baseCurrency', 'currencySymbol',
            'dueSoon', 'subscriptions', 'categoryBreakdown', 'fxUpdatedAt',
            'totalMonthlyMyr', 'totalYearlyMyr'
        ));
    }
}
