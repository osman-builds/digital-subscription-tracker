<?php

namespace App\Services;

use App\Models\AiExtractionAudit;
use App\Models\BillingEvent;
use App\Models\InsightAlert;
use App\Models\Subscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\User;

/**
 * Maintains SubShield's immutable billing ledger, price-creep audit log, and AI verification linkage.
 */
class SubscriptionLedgerService
{
    public function __construct(private readonly FxRateService $fx) {}

    /**
     * Record a billing event with an immutable FX rate snapshot in the user's base currency.
     */
    public function recordEvent(
        Subscription $subscription,
        User $user,
        string $source = 'manual',
        ?string $billedAt = null
    ): BillingEvent {
        $settings = $user->getSettings();
        $baseCurrency = strtoupper((string) ($settings->base_currency ?? 'MYR'));
        $subCurrency = strtoupper((string) ($subscription->currency ?? 'MYR'));
        $amount = (float) $subscription->amount;

        $rate = $this->fx->rate($subCurrency, $baseCurrency);
        $amountBase = round($amount * $rate, 2);

        return BillingEvent::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => $subCurrency,
            'fx_rate_snapshot' => $rate,
            'amount_base' => $amountBase,
            'base_currency' => $baseCurrency,
            'billing_cycle' => (string) $subscription->billing_cycle,
            'billed_at' => $billedAt ?? $subscription->start_date?->toDateString() ?? now()->toDateString(),
            'source' => $source,
        ]);
    }

    /**
     * Detect price changes on matching currency edits/scans and raise price-creep alerts when cost rises.
     */
    public function recordPriceChangeIfChanged(
        Subscription $subscription,
        float $oldAmount,
        string $oldCurrency,
        float $newAmount,
        string $newCurrency,
        string $detectedVia = 'manual_edit'
    ): ?SubscriptionPriceHistory {
        $oldCur = strtoupper(trim($oldCurrency));
        $newCur = strtoupper(trim($newCurrency));

        if ($oldCur !== $newCur || abs($newAmount - $oldAmount) < 0.01) {
            return null;
        }

        $changeAmount = round($newAmount - $oldAmount, 2);
        $changePercent = $oldAmount > 0
            ? round(($changeAmount / $oldAmount) * 100, 2)
            : 0.0;

        $history = SubscriptionPriceHistory::create([
            'subscription_id' => $subscription->id,
            'old_amount' => round($oldAmount, 2),
            'new_amount' => round($newAmount, 2),
            'currency' => $newCur,
            'change_amount' => $changeAmount,
            'change_percent' => $changePercent,
            'detected_via' => $detectedVia,
            'recorded_at' => now(),
        ]);

        if ($changeAmount > 0) {
            $this->raisePriceCreepAlert($subscription, $oldAmount, $newAmount, $newCur, $changePercent);
        }

        return $history;
    }

    /**
     * Bind a verified AI extraction audit record strictly within the owning user's tenant boundary.
     */
    public function linkAudit(int $userId, ?int $auditId, int $subscriptionId, string $status): void
    {
        if ($auditId === null || $auditId <= 0) {
            return;
        }

        AiExtractionAudit::where('id', $auditId)
            ->where('user_id', $userId)
            ->update([
                'subscription_id' => $subscriptionId,
                'status' => $status,
                'verified_at' => now(),
            ]);
    }

    private function raisePriceCreepAlert(
        Subscription $subscription,
        float $oldAmount,
        float $newAmount,
        string $currency,
        float $changePercent
    ): void {
        $symbol = $this->fx->symbol($currency);
        $formattedOld = number_format($oldAmount, 2);
        $formattedNew = number_format($newAmount, 2);
        $formattedPct = number_format($changePercent, 1);

        InsightAlert::updateOrCreate(
            [
                'user_id' => $subscription->user_id,
                'dedupe_key' => "price_creep:{$subscription->id}:".now()->format('Y-m'),
            ],
            [
                'subscription_id' => $subscription->id,
                'alert_type' => 'price_creep',
                'severity' => $changePercent >= 15 ? 'critical' : 'warning',
                'title' => "Price Hike Detected: {$subscription->name} (+{$formattedPct}%)",
                'message' => "{$subscription->name} increased from {$symbol} {$formattedOld} to {$symbol} {$formattedNew} ({$currency}). Consider reviewing plan tiers or cancellation options.",
                'is_read' => false,
                'dismissed_at' => null,
            ]
        );
    }
}
