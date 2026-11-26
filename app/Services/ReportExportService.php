<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generates arranged CSV spreadsheet exports with CWE-1236 formula-injection protection.
 */
class ReportExportService
{
    private const DATE_FORMAT = 'Y-m-d';

    private const DEFAULT_SUB_CATEGORY = 'Uncategorized';

    private const DEFAULT_EXP_CATEGORY = 'Uncategorised';

    private const COL_CATEGORY = 'Category';

    private const LABEL_SUBSCRIPTION = 'Subscription';

    /**
     * Stream subscriptions portfolio as an arranged CSV spreadsheet.
     *
     * @param  Collection<int, mixed>  $subscriptions
     */
    public function streamSubscriptionsCsv(User $user, Collection $subscriptions, string $baseCurrency): StreamedResponse
    {
        $filename = 'subshield-subscriptions-'.now()->format(self::DATE_FORMAT).'.csv';

        return $this->streamCsvDownload($filename, function ($handle) use ($user, $subscriptions, $baseCurrency): void {
            $this->writeSubscriptionsReport($handle, $user, $subscriptions, $baseCurrency);
        });
    }

    /**
     * Stream yearly expenditure report as an arranged CSV spreadsheet.
     *
     * @param  array{
     *     yearly_total: float,
     *     monthly: Collection<int, mixed>,
     *     category_breakdown: Collection<string, float>,
     *     subscriptions: Collection<int, mixed>,
     *     billing_events: Collection<int, mixed>
     * }  $report
     */
    public function streamExpenditureCsv(User $user, int $year, string $baseCurrency, array $report): StreamedResponse
    {
        $filename = "subshield-expenditure-{$year}-".now()->format(self::DATE_FORMAT).'.csv';

        return $this->streamCsvDownload($filename, function ($handle) use ($user, $year, $baseCurrency, $report): void {
            $this->writeExpenditureReport($handle, $user, $year, $baseCurrency, $report);
        });
    }

    private function streamCsvDownload(string $filename, callable $writer): StreamedResponse
    {
        return response()->stream(function () use ($writer): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            $writer($handle);
            fclose($handle);
        }, 200, $this->csvDownloadHeaders($filename));
    }

    /**
     * @param  resource  $handle
     * @param  Collection<int, mixed>  $subscriptions
     */
    private function writeSubscriptionsReport($handle, User $user, Collection $subscriptions, string $baseCurrency): void
    {
        $activeSubs = $subscriptions->where('status', 'active');
        $totalMonthly = (float) $activeSubs->sum(fn ($s) => $s->monthlyAmountIn($baseCurrency));
        $totalYearly = (float) $activeSubs->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency));
        $activeCount = $activeSubs->count();

        // Portfolio Metadata Header Block
        fputcsv($handle, ['SubShield - Subscription Financial Portfolio Report']);
        fputcsv($handle, ['Exported Date', now()->format('Y-m-d H:i:s')]);
        fputcsv($handle, ['Account Name', $this->sanitizeCsv($user->name)]);
        fputcsv($handle, ['Account Email', $this->sanitizeCsv($user->email)]);
        fputcsv($handle, ['Base Currency', $baseCurrency]);
        fputcsv($handle, ['Active Subscriptions', $activeCount]);
        fputcsv($handle, ["Total Monthly Commitment ({$baseCurrency})", number_format($totalMonthly, 2, '.', '')]);
        fputcsv($handle, ["Total Projected Annual ({$baseCurrency})", number_format($totalYearly, 2, '.', '')]);
        fputcsv($handle, []);

        // Main Subscriptions Table Headers
        fputcsv($handle, [
            '#',
            'Service Name',
            'Website URL',
            self::COL_CATEGORY,
            'Status',
            'Original Amount',
            'Currency',
            'Billing Cycle',
            "Monthly Equivalent ({$baseCurrency})",
            "Yearly Equivalent ({$baseCurrency})",
            'Start Date',
            'Next Billing Date',
            'Trial Status',
            'Notes',
        ]);

        $index = 1;
        foreach ($subscriptions as $sub) {
            fputcsv($handle, [
                $index++,
                $this->sanitizeCsv($sub->name),
                $this->sanitizeCsv($sub->website_url ?? ''),
                $this->sanitizeCsv($sub->category !== null ? $sub->category->name : self::DEFAULT_SUB_CATEGORY),
                ucfirst((string) $sub->status),
                number_format((float) $sub->amount, 2, '.', ''),
                $sub->currency,
                $sub->billingCycleLabel(),
                number_format($sub->monthlyAmountIn($baseCurrency), 2, '.', ''),
                number_format($sub->yearlyAmountIn($baseCurrency), 2, '.', ''),
                $sub->start_date?->format(self::DATE_FORMAT) ?? '',
                $sub->next_billing_date?->format(self::DATE_FORMAT) ?? '',
                $this->formatTrialStatus($sub),
                $this->sanitizeCsv($sub->notes ?? ''),
            ]);
        }

        // Summary Totals Row
        fputcsv($handle, [
            'TOTAL',
            "{$activeCount} Active Services",
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($totalMonthly, 2, '.', ''),
            number_format($totalYearly, 2, '.', ''),
            '',
            '',
            '',
            '',
        ]);

        $this->writeSubscriptionsCategoryBreakdown($handle, $activeSubs, $baseCurrency, $totalMonthly);
    }

    /**
     * @param  resource  $handle
     * @param  Collection<int, mixed>  $activeSubs
     */
    private function writeSubscriptionsCategoryBreakdown($handle, Collection $activeSubs, string $baseCurrency, float $totalMonthly): void
    {
        $categoryGroups = $activeSubs->groupBy(fn ($s) => $s->category !== null ? $s->category->name : self::DEFAULT_SUB_CATEGORY);
        if ($categoryGroups->isEmpty()) {
            return;
        }

        fputcsv($handle, []);
        fputcsv($handle, ['CATEGORY SPEND BREAKDOWN']);
        fputcsv($handle, [self::COL_CATEGORY, 'Active Subscriptions', "Monthly Spend ({$baseCurrency})", "Yearly Spend ({$baseCurrency})", 'Share of Spend (%)']);
        foreach ($categoryGroups as $catName => $group) {
            $catMonthly = (float) $group->sum(fn ($s) => $s->monthlyAmountIn($baseCurrency));
            $catYearly = (float) $group->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency));
            $share = $totalMonthly > 0 ? round(($catMonthly / $totalMonthly) * 100, 1) : 0.0;
            fputcsv($handle, [
                $this->sanitizeCsv($catName),
                $group->count(),
                number_format($catMonthly, 2, '.', ''),
                number_format($catYearly, 2, '.', ''),
                $share.'%',
            ]);
        }
    }

    private function formatTrialStatus(mixed $sub): string
    {
        if (! (bool) $sub->is_trial) {
            return 'Standard';
        }

        return $sub->trial_ends_at ? 'Trial until '.$sub->trial_ends_at->format(self::DATE_FORMAT) : 'Active Trial';
    }

    /**
     * @param  resource  $handle
     * @param  array{
     *     yearly_total: float,
     *     monthly: Collection<int, mixed>,
     *     category_breakdown: Collection<string, float>,
     *     subscriptions: Collection<int, mixed>,
     *     billing_events: Collection<int, mixed>
     * }  $report
     */
    private function writeExpenditureReport($handle, User $user, int $year, string $baseCurrency, array $report): void
    {
        $yearlyTotal = (float) $report['yearly_total'];

        // Header metadata block
        fputcsv($handle, ["SubShield - Annual Expenditure Report ({$year})"]);
        fputcsv($handle, ['Exported Date', now()->format('Y-m-d H:i:s')]);
        fputcsv($handle, ['Account', $this->sanitizeCsv($user->name.' ('.$user->email.')')]);
        fputcsv($handle, ['Base Currency', $baseCurrency]);
        fputcsv($handle, ['Selected Year', $year]);
        fputcsv($handle, ["Total Annual Spend ({$baseCurrency})", number_format($yearlyTotal, 2, '.', '')]);
        fputcsv($handle, ["Average Monthly Spend ({$baseCurrency})", number_format($yearlyTotal / 12, 2, '.', '')]);
        fputcsv($handle, []);

        $this->writeExpenditureMonthlyBreakdown($handle, $report['monthly'], $year, $yearlyTotal, $baseCurrency);
        $this->writeExpenditureCategoryBreakdown($handle, $report['category_breakdown'], $yearlyTotal, $baseCurrency);
        $this->writeExpenditureSubscriptionsBreakdown($handle, $report['subscriptions'], $baseCurrency);
        $this->writeExpenditureBillingEvents($handle, $report['billing_events'], $baseCurrency);
    }

    /**
     * @param  resource  $handle
     * @param  Collection<int, mixed>  $monthly
     */
    private function writeExpenditureMonthlyBreakdown($handle, Collection $monthly, int $year, float $yearlyTotal, string $baseCurrency): void
    {
        fputcsv($handle, ['MONTHLY EXPENDITURE BREAKDOWN']);
        fputcsv($handle, ['Month', "Spend ({$baseCurrency})", '% of Annual']);
        foreach ($monthly as $m) {
            $pct = $yearlyTotal > 0 ? round(($m['total'] / $yearlyTotal) * 100, 1) : 0.0;
            fputcsv($handle, [$m['month'].' '.$year, number_format($m['total'], 2, '.', ''), $pct.'%']);
        }
        fputcsv($handle, ['TOTAL', number_format($yearlyTotal, 2, '.', ''), '100.0%']);
        fputcsv($handle, []);
    }

    /**
     * @param  resource  $handle
     * @param  Collection<string, float>  $categoryBreakdown
     */
    private function writeExpenditureCategoryBreakdown($handle, Collection $categoryBreakdown, float $yearlyTotal, string $baseCurrency): void
    {
        fputcsv($handle, ['CATEGORY EXPENDITURE BREAKDOWN']);
        fputcsv($handle, [self::COL_CATEGORY, "Yearly Spend ({$baseCurrency})", '% of Annual']);
        foreach ($categoryBreakdown as $catName => $catTotal) {
            $pct = $yearlyTotal > 0 ? round(($catTotal / $yearlyTotal) * 100, 1) : 0.0;
            fputcsv($handle, [$this->sanitizeCsv($catName), number_format($catTotal, 2, '.', ''), $pct.'%']);
        }
        fputcsv($handle, []);
    }

    /**
     * @param  resource  $handle
     * @param  Collection<int, mixed>  $activeSubscriptions
     */
    private function writeExpenditureSubscriptionsBreakdown($handle, Collection $activeSubscriptions, string $baseCurrency): void
    {
        fputcsv($handle, ['ANNUAL SUBSCRIPTION COSTS']);
        fputcsv($handle, ['#', self::LABEL_SUBSCRIPTION, self::COL_CATEGORY, 'Cycle', 'Original Price', 'Original Currency', "Monthly ({$baseCurrency})", "Yearly ({$baseCurrency})"]);
        $idx = 1;
        foreach ($activeSubscriptions->sortByDesc(fn ($s) => $s->yearlyAmountIn($baseCurrency)) as $sub) {
            fputcsv($handle, [
                $idx++,
                $this->sanitizeCsv($sub->name),
                $this->sanitizeCsv($sub->category !== null ? $sub->category->name : self::DEFAULT_EXP_CATEGORY),
                $sub->billingCycleLabel(),
                number_format((float) $sub->amount, 2, '.', ''),
                $sub->currency,
                number_format($sub->monthlyAmountIn($baseCurrency), 2, '.', ''),
                number_format($sub->yearlyAmountIn($baseCurrency), 2, '.', ''),
            ]);
        }
        fputcsv($handle, []);
    }

    /**
     * @param  resource  $handle
     * @param  Collection<int, mixed>  $billingEvents
     */
    private function writeExpenditureBillingEvents($handle, Collection $billingEvents, string $baseCurrency): void
    {
        if ($billingEvents->isEmpty()) {
            return;
        }

        fputcsv($handle, ['BILLING LEDGER AUDIT TRAIL (FX SNAPSHOTS)']);
        fputcsv($handle, ['Billed Date', self::LABEL_SUBSCRIPTION, 'Original Amount', 'Currency', 'FX Rate Snapshot', "Recorded Base ({$baseCurrency})", 'Source']);
        foreach ($billingEvents as $ev) {
            fputcsv($handle, [
                $ev->billed_at?->format(self::DATE_FORMAT) ?? '',
                $this->sanitizeCsv($ev->subscription !== null ? $ev->subscription->name : self::LABEL_SUBSCRIPTION),
                number_format((float) $ev->amount, 2, '.', ''),
                $ev->currency,
                number_format((float) $ev->fx_rate_snapshot, 6, '.', ''),
                number_format((float) $ev->amount_base, 2, '.', ''),
                $ev->source,
            ]);
        }
    }

    /** @return array<string, string> */
    private function csvDownloadHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
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
