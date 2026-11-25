<?php

namespace App\Http\Controllers;

use App\Models\BillingEvent;
use App\Models\Subscription;
use App\Services\FinancialIntelligenceService;
use App\Services\FxRateService;
use App\Services\ReportExportService;
use App\Services\SandboxService;
use App\Services\SandboxSubscriptionStore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenditureController extends Controller
{
    public function index(
        Request $request,
        FxRateService $fx,
        SandboxSubscriptionStore $sandboxSubscriptions,
        FinancialIntelligenceService $intelligenceService,
        SandboxService $sandbox
    ) {
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
        $monthly = $this->computeMonthlyBreakdown($activeSubscriptions, $year, $baseCurrency);

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
        $intelligence = $intelligenceService->analyze($user, $activeSubscriptions, $settings, false);
        $billingEvents = $user->isSandbox()
            ? $sandbox->getBillingEvents($userId, null, $activeSubscriptions)
            : BillingEvent::with('subscription')
                ->where('user_id', $userId)
                ->latest('billed_at')
                ->latest('id')
                ->limit(12)
                ->get();

        return view('expenditure.index', compact(
            'monthly', 'yearlyTotal', 'categoryBreakdown',
            'subscriptionYearly', 'year', 'years', 'fxUpdatedAt',
            'baseCurrency', 'currencySymbol', 'intelligence', 'billingEvents'
        ));
    }

    /** Stream yearly expenditure report as an arranged CSV spreadsheet */
    public function exportCsv(
        Request $request,
        SandboxSubscriptionStore $sandboxSubscriptions,
        SandboxService $sandbox,
        ReportExportService $exporter
    ): StreamedResponse {
        $year = (int) $request->get('year', now()->year);
        $user = auth()->user();
        $userId = $user->id;
        $settings = $user->getSettings();
        $baseCurrency = $settings->base_currency ?? 'MYR';

        $activeSubscriptions = $user->isSandbox()
            ? $sandboxSubscriptions->active($userId)
            : Subscription::with('category')->forUser($userId)->active()->get();

        $monthly = $this->computeMonthlyBreakdown($activeSubscriptions, $year, $baseCurrency);

        $yearlyTotal = round($monthly->sum('total'), 2);

        $categoryBreakdown = $activeSubscriptions
            ->groupBy(fn ($s) => $s->category->name ?? 'Uncategorised')
            ->map(fn ($group) => round($group->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency)), 2))
            ->sortDesc();

        $billingEvents = $user->isSandbox()
            ? $sandbox->getBillingEvents($userId, null, $activeSubscriptions)
            : BillingEvent::with('subscription')
                ->where('user_id', $userId)
                ->latest('billed_at')
                ->latest('id')
                ->limit(50)
                ->get();

        return $exporter->streamExpenditureCsv($user, $year, $baseCurrency, [
            'yearly_total' => $yearlyTotal,
            'monthly' => $monthly,
            'category_breakdown' => $categoryBreakdown,
            'subscriptions' => $activeSubscriptions,
            'billing_events' => $billingEvents,
        ]);
    }

    /** Generate printable PDF report view for annual expenditure */
    public function exportPdf(
        Request $request,
        FxRateService $fx,
        SandboxSubscriptionStore $sandboxSubscriptions,
        FinancialIntelligenceService $intelligenceService,
        SandboxService $sandbox
    ): View {
        $year = (int) $request->get('year', now()->year);
        $user = auth()->user();
        $userId = $user->id;
        $settings = $user->getSettings();
        $baseCurrency = $settings->base_currency ?? 'MYR';
        $currencySymbol = $fx->symbol($baseCurrency);

        $activeSubscriptions = $user->isSandbox()
            ? $sandboxSubscriptions->active($userId)
            : Subscription::with('category')->forUser($userId)->active()->get();

        $monthly = $this->computeMonthlyBreakdown($activeSubscriptions, $year, $baseCurrency);

        $yearlyTotal = round($monthly->sum('total'), 2);
        $categoryBreakdown = $activeSubscriptions
            ->groupBy(fn ($s) => $s->category->name ?? 'Uncategorised')
            ->map(fn ($group) => round($group->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency)), 2))
            ->sortDesc();

        $subscriptionYearly = $activeSubscriptions
            ->sortByDesc(fn ($s) => $s->yearlyAmountIn($baseCurrency))
            ->values();

        $intelligence = $intelligenceService->analyze($user, $activeSubscriptions, $settings, false);
        $billingEvents = $user->isSandbox()
            ? $sandbox->getBillingEvents($userId, null, $activeSubscriptions)
            : BillingEvent::with('subscription')
                ->where('user_id', $userId)
                ->latest('billed_at')
                ->latest('id')
                ->limit(20)
                ->get();

        return view('expenditure.export_pdf', compact(
            'user', 'year', 'baseCurrency', 'currencySymbol', 'monthly',
            'yearlyTotal', 'categoryBreakdown', 'subscriptionYearly',
            'intelligence', 'billingEvents'
        ));
    }

    /** @return Collection<int, array{month: string, total: float}> */
    private function computeMonthlyBreakdown(Collection $activeSubscriptions, int $year, string $baseCurrency): Collection
    {
        return collect(range(1, 12))->map(function (int $month) use ($activeSubscriptions, $year, $baseCurrency): array {
            $monthLabel = Carbon::create($year, $month)->format('M');
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $total = (float) $activeSubscriptions->sum(function (Subscription $sub) use ($monthEnd, $baseCurrency): float {
                if ($sub->start_date && $sub->start_date->gt($monthEnd)) {
                    return 0.0;
                }

                return (float) $sub->monthlyAmountIn($baseCurrency);
            });

            return [
                'month' => $monthLabel,
                'total' => round($total, 2),
            ];
        });
    }
}
