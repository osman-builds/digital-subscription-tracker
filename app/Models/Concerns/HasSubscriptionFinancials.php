<?php

namespace App\Models\Concerns;

use App\Services\FxRateService;

trait HasSubscriptionFinancials
{
    /** Effective amount converted to target currency (defaults to MYR) */
    public function effectiveAmountIn(?string $targetCurrency = null): float
    {
        $target = $targetCurrency ? strtoupper(trim($targetCurrency)) : 'MYR';

        if ($target === 'MYR') {
            return $this->effectiveMyr();
        }

        if ($this->currency === $target) {
            return (float) $this->amount;
        }

        return app(FxRateService::class)->convert(
            (float) $this->amount,
            $this->currency ?? 'MYR',
            $target
        );
    }

    /** Monthly equivalent in target currency */
    public function monthlyAmountIn(?string $targetCurrency = null): float
    {
        $effective = $this->effectiveAmountIn($targetCurrency);

        return match ($this->billing_cycle) {
            'weekly' => $effective * 52 / 12,
            'monthly' => $effective,
            'quarterly' => $effective / 3,
            'semi_annual' => $effective / 6,
            'annual' => $effective / 12,
            default => $effective,
        };
    }

    /** Yearly equivalent in target currency */
    public function yearlyAmountIn(?string $targetCurrency = null): float
    {
        $effective = $this->effectiveAmountIn($targetCurrency);

        return match ($this->billing_cycle) {
            'weekly' => $effective * 52,
            'monthly' => $effective * 12,
            'quarterly' => $effective * 4,
            'semi_annual' => $effective * 2,
            'annual' => $effective,
            default => $effective * 12,
        };
    }

    /** Effective MYR amount per billing period */
    public function effectiveMyr(): float
    {
        return (float) ($this->amount_myr ?? $this->amount);
    }

    /** Monthly equivalent in MYR */
    public function monthlyAmountMyr(): float
    {
        return $this->monthlyAmountIn('MYR');
    }

    /** Yearly equivalent in MYR */
    public function yearlyAmountMyr(): float
    {
        return $this->yearlyAmountIn('MYR');
    }

    /** Legacy alias — monthly in original currency (for display) */
    public function monthlyAmount(): float
    {
        return match ($this->billing_cycle) {
            'weekly' => (float) $this->amount * 52 / 12,
            'monthly' => (float) $this->amount,
            'quarterly' => (float) $this->amount / 3,
            'semi_annual' => (float) $this->amount / 6,
            'annual' => (float) $this->amount / 12,
            default => (float) $this->amount,
        };
    }

    /** Legacy alias — yearly in original currency */
    public function yearlyAmount(): float
    {
        return match ($this->billing_cycle) {
            'weekly' => (float) $this->amount * 52,
            'monthly' => (float) $this->amount * 12,
            'quarterly' => (float) $this->amount * 4,
            'semi_annual' => (float) $this->amount * 2,
            'annual' => (float) $this->amount,
            default => (float) $this->amount * 12,
        };
    }
}
