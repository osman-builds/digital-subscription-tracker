<?php

namespace App\Models;

use App\Services\FxRateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'amount',
        'currency',
        'amount_myr',
        'billing_cycle',
        'start_date',
        'next_billing_date',
        'status',
        'website_url',
        'logo_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'category_id' => 'integer',
            'start_date' => 'date',
            'next_billing_date' => 'date',
            'amount' => 'decimal:2',
            'amount_myr' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Cost helpers: Dynamic base currency conversion with MYR fallbacks
    // -------------------------------------------------------------------------

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

    /** Human-readable billing cycle */
    public function billingCycleLabel(): string
    {
        return match ($this->billing_cycle) {
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annual' => 'Semi-Annual',
            'annual' => 'Annual',
            default => ucfirst($this->billing_cycle),
        };
    }

    /** Days until next billing date (negative = overdue) */
    public function daysUntilRenewal(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->next_billing_date, false);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('next_billing_date', [
                now()->toDateString(),
                now()->addDays($days)->toDateString(),
            ]);
    }
}
