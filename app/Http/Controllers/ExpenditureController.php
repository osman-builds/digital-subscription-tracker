<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\FxRateService;
use Illuminate\Http\Request;

class ExpenditureController extends Controller
{
    public function index(Request $request, FxRateService $fx)
    {
        $year   = (int) $request->get('year', now()->year);
        $userId = auth()->id();

        $activeSubscriptions = Subscription::with('category')
            ->forUser($userId)
            ->active()
            ->get();

        // ── Monthly breakdown ────────────────────────────────────────────────
        // For each month of the selected year, calculate total MYR spend
        $monthly = collect(range(1, 12))->map(function (int $month) use ($activeSubscriptions, $year) {
            $monthLabel = \Carbon\Carbon::create($year, $month)->format('M');

            // Sum subscriptions that were active during this month
            // (started on or before last day of month, not cancelled yet)
            $total = $activeSubscriptions->sum(function (Subscription $sub) use ($year, $month) {
                $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $monthEnd   = $monthStart->copy()->endOfMonth();

                if ($sub->start_date->gt($monthEnd)) {
                    return 0; // not started yet
                }

                return $sub->monthlyAmountMyr();
            });

            return [
                'month' => $monthLabel,
                'total' => round($total, 2),
            ];
        });

        // ── Yearly summary ───────────────────────────────────────────────────
        $yearlyTotal = $activeSubscriptions->sum(fn ($s) => $s->yearlyAmountMyr());

        // ── Category breakdown for doughnut chart ───────────────────────────
        $categoryBreakdown = $activeSubscriptions
            ->groupBy(fn ($s) => $s->category->name ?? 'Uncategorised')
            ->map(fn ($group) => round($group->sum(fn ($s) => $s->yearlyAmountMyr()), 2))
            ->sortDesc();

        // ── Per-subscription yearly cost table ──────────────────────────────
        $subscriptionYearly = $activeSubscriptions
            ->sortByDesc(fn ($s) => $s->yearlyAmountMyr())
            ->values();

        // Available years for the year selector
        $years = range(now()->year - 2, now()->year + 1);

        $fxUpdatedAt = $fx->lastUpdated();

        return view('expenditure.index', compact(
            'monthly', 'yearlyTotal', 'categoryBreakdown',
            'subscriptionYearly', 'year', 'years', 'fxUpdatedAt'
        ));
    }
}
