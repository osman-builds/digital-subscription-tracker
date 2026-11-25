<?php

namespace App\Http\Controllers;

use App\Models\BillingEvent;
use App\Models\Subscription;
use App\Services\FinancialIntelligenceService;
use App\Services\FxRateService;
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
        FxRateService $fx,
        SandboxSubscriptionStore $sandboxSubscriptions,
        SandboxService $sandbox
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

        $filename = "subshield-expenditure-{$year}-".now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($year, $user, $baseCurrency, $yearlyTotal, $monthly, $categoryBreakdown, $activeSubscriptions, $billingEvents): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Header metadata block
            fputcsv($handle, ["SubShield - Annual Expenditure Report ({$year})"]);
            fputcsv($handle, ['Exported Date', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Account', $this->sanitizeCsv($user->name.' ('.$user->email.')')]);
            fputcsv($handle, ['Base Currency', $baseCurrency]);
            fputcsv($handle, ['Selected Year', $year]);
            fputcsv($handle, ["Total Annual Spend ({$baseCurrency})", number_format($yearlyTotal, 2, '.', '')]);
            fputcsv($handle, ["Average Monthly Spend ({$baseCurrency})", number_format($yearlyTotal / 12, 2, '.', '')]);
            fputcsv($handle, []);

            // 1. Monthly Breakdown Table
            fputcsv($handle, ['MONTHLY EXPENDITURE BREAKDOWN']);
            fputcsv($handle, ['Month', "Spend ({$baseCurrency})", '% of Annual']);
            foreach ($monthly as $m) {
                $pct = $yearlyTotal > 0 ? round(($m['total'] / $yearlyTotal) * 100, 1) : 0.0;
                fputcsv($handle, [$m['month'].' '.$year, number_format($m['total'], 2, '.', ''), $pct.'%']);
            }
            fputcsv($handle, ['TOTAL', number_format($yearlyTotal, 2, '.', ''), '100.0%']);
            fputcsv($handle, []);

            // 2. Category Breakdown Table
            fputcsv($handle, ['CATEGORY EXPENDITURE BREAKDOWN']);
            fputcsv($handle, ['Category', "Yearly Spend ({$baseCurrency})", '% of Annual']);
            foreach ($categoryBreakdown as $catName => $catTotal) {
                $pct = $yearlyTotal > 0 ? round(($catTotal / $yearlyTotal) * 100, 1) : 0.0;
                fputcsv($handle, [$this->sanitizeCsv($catName), number_format($catTotal, 2, '.', ''), $pct.'%']);
            }
            fputcsv($handle, []);

            // 3. Subscriptions Annual Cost
            fputcsv($handle, ['ANNUAL SUBSCRIPTION COSTS']);
            fputcsv($handle, ['#', 'Subscription', 'Category', 'Cycle', 'Original Price', 'Original Currency', "Monthly ({$baseCurrency})", "Yearly ({$baseCurrency})"]);
            $idx = 1;
            foreach ($activeSubscriptions->sortByDesc(fn ($s) => $s->yearlyAmountIn($baseCurrency)) as $sub) {
                fputcsv($handle, [
                    $idx++,
                    $this->sanitizeCsv($sub->name),
                    $this->sanitizeCsv($sub->category?->name ?? 'Uncategorised'),
                    $sub->billingCycleLabel(),
                    number_format((float) $sub->amount, 2, '.', ''),
                    $sub->currency,
                    number_format($sub->monthlyAmountIn($baseCurrency), 2, '.', ''),
                    number_format($sub->yearlyAmountIn($baseCurrency), 2, '.', ''),
                ]);
            }
            fputcsv($handle, []);

            // 4. Billing Ledger Events
            if ($billingEvents->isNotEmpty()) {
                fputcsv($handle, ['BILLING LEDGER AUDIT TRAIL (FX SNAPSHOTS)']);
                fputcsv($handle, ['Billed Date', 'Subscription', 'Original Amount', 'Currency', 'FX Rate Snapshot', "Recorded Base ({$baseCurrency})", 'Source']);
                foreach ($billingEvents as $ev) {
                    fputcsv($handle, [
                        $ev->billed_at?->format('Y-m-d') ?? '',
                        $this->sanitizeCsv($ev->subscription?->name ?? 'Subscription'),
                        number_format((float) $ev->amount, 2, '.', ''),
                        $ev->currency,
                        number_format((float) $ev->fx_rate_snapshot, 6, '.', ''),
                        number_format((float) $ev->amount_base, 2, '.', ''),
                        $ev->source,
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
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

    /** Neutralize CSV formula injection (CWE-1236 / OWASP) */
    private function sanitizeCsv(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
