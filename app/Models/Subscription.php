<?php

namespace App\Models;

use App\Models\Concerns\HasSubscriptionFinancials;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $name
 * @property string|null $description
 * @property numeric-string|float $amount
 * @property string $currency
 * @property numeric-string|float|null $amount_myr
 * @property string $billing_cycle
 * @property Carbon|null $start_date
 * @property Carbon|null $next_billing_date
 * @property string $status
 * @property bool $is_trial
 * @property Carbon|null $trial_ends_at
 * @property string|null $website_url
 * @property string|null $cancellation_url
 * @property string|null $logo_url
 * @property string|null $notes
 * @property string|null $input_source
 * @property-read Category|null $category
 * @property-read User|null $user
 */
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

    /**
     * @return array<string, string>
     */
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

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<BillingEvent, $this>
     */
    public function billingEvents(): HasMany
    {
        return $this->hasMany(BillingEvent::class);
    }

    /**
     * @return HasMany<SubscriptionPriceHistory, $this>
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(SubscriptionPriceHistory::class);
    }

    /**
     * @return HasMany<InsightAlert, $this>
     */
    public function insightAlerts(): HasMany
    {
        return $this->hasMany(InsightAlert::class);
    }

    /**
     * @return HasMany<AiExtractionAudit, $this>
     */
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

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('status', 'active')
            ->whereBetween('next_billing_date', [
                now()->toDateString(),
                now()->addDays($days)->toDateString(),
            ]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeTrialExpiringSoon(Builder $query, int $days = 3): Builder
    {
        return $query->where('status', 'active')
            ->where('is_trial', true)
            ->whereBetween('trial_ends_at', [
                now()->toDateString(),
                now()->addDays($days)->toDateString(),
            ]);
    }
}
