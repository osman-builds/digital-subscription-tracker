<?php

namespace App\Models;

use App\Models\Concerns\HasSubscriptionFinancials;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasSubscriptionFinancials;

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
        'is_trial',
        'trial_ends_at',
        'website_url',
        'cancellation_url',
        'logo_url',
        'notes',
        'input_source',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'category_id' => 'integer',
            'start_date' => 'date',
            'next_billing_date' => 'date',
            'trial_ends_at' => 'date',
            'is_trial' => 'boolean',
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

    public function billingEvents(): HasMany
    {
        return $this->hasMany(BillingEvent::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(SubscriptionPriceHistory::class);
    }

    public function insightAlerts(): HasMany
    {
        return $this->hasMany(InsightAlert::class);
    }

    public function aiExtractionAudits(): HasMany
    {
        return $this->hasMany(AiExtractionAudit::class);
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

    /** Advance billing date to next renewal cycle */
    public function advanceBillingCycle(): self
    {
        $current = $this->next_billing_date ?? now();
        $base = $current->isPast() ? now() : $current;

        $nextDate = match ($this->billing_cycle) {
            'weekly' => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonth(),
            'quarterly' => $base->copy()->addMonths(3),
            'semi_annual' => $base->copy()->addMonths(6),
            'annual' => $base->copy()->addYear(),
            default => $base->copy()->addMonth(),
        };

        $this->next_billing_date = $nextDate;
        if ($this->is_trial && $this->trial_ends_at?->isPast()) {
            $this->is_trial = false;
        }

        $this->save();

        return $this;
    }

    /** Whether free trial is currently active */
    public function isTrialActive(): bool
    {
        return (bool) $this->is_trial && $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /** Days until free trial ends */
    public function daysUntilTrialEnds(): ?int
    {
        return $this->trial_ends_at ? (int) now()->startOfDay()->diffInDays($this->trial_ends_at->startOfDay(), false) : null;
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

    public function scopeTrialExpiringSoon($query, int $days = 3)
    {
        return $query->where('status', 'active')
            ->where('is_trial', true)
            ->whereBetween('trial_ends_at', [
                now()->toDateString(),
                now()->addDays($days)->toDateString(),
            ]);
    }
}
