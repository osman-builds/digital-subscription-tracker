<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\FxRateService;
use App\Services\SandboxSubscriptionStore;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenditureController extends Controller
{
    public function index(Request $request, FxRateService $fx, SandboxSubscriptionStore $sandboxSubscriptions)
    {
        $year = (int) $request->get('year', now()->year);
        $user = auth()->user();
        $userId = $user->id;
        $settings = $user->getSettings();
        $baseCurrency = $settings->base_currency ?? 'MYR';
        $currencySymbol = $fx->symbol($baseCurrency);

        $activeSubscriptions = $user->isSandbox()
            ? $sandboxSubscriptions->active($userId)
            : Subscription::with('category')->forUser($userId)->active()->get();

        // ── Monthly breakdown ────────────────────────────────────────────────
        // For each month of the selected year, calculate total spend in base currency
        $monthly = collect(range(1, 12))->map(function (int $month) use ($activeSubscriptions, $year, $baseCurrency) {
            $monthLabel = Carbon::create($year, $month)->format('M');

            // Sum subscriptions that were active during this month
            // (started on or before last day of month, not cancelled yet)
            $total = $activeSubscriptions->sum(function (Subscription $sub) use ($year, $month, $baseCurrency) {
                $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();

                if ($sub->start_date->gt($monthEnd)) {
                    return 0; // not started yet
                }

                return $sub->monthlyAmountIn($baseCurrency);
            });

            return [
                'month' => $monthLabel,
                'total' => round($total, 2),
            ];
        });

        // ── Yearly summary ───────────────────────────────────────────────────
        $yearlyTotal = $activeSubscriptions->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency));

        // ── Category breakdown for doughnut chart ───────────────────────────
        $categoryBreakdown = $activeSubscriptions
            ->groupBy(fn ($s) => $s->category->name ?? 'Uncategorised')
            ->map(fn ($group) => round($group->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency)), 2))
            ->sortDesc();

        // ── Per-subscription yearly cost table ──────────────────────────────
        $subscriptionYearly = $activeSubscriptions
            ->sortByDesc(fn ($s) => $s->yearlyAmountIn($baseCurrency))
            ->values();

        // Available years for the year selector
        $years = range(now()->year - 2, now()->year + 1);

        $fxUpdatedAt = $fx->lastUpdated();

        return view('expenditure.index', compact(
            'monthly', 'yearlyTotal', 'categoryBreakdown',
            'subscriptionYearly', 'year', 'years', 'fxUpdatedAt',
            'baseCurrency', 'currencySymbol'
        ));
    }
}
