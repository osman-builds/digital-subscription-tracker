<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $subscription_id
 * @property numeric-string|float $amount
 * @property string $currency
 * @property numeric-string|float $fx_rate_snapshot
 * @property numeric-string|float $amount_base
 * @property string $base_currency
 * @property string $billing_cycle
 * @property Carbon|null $billed_at
 * @property string $source
 * @property-read User|null $user
 * @property-read Subscription|null $subscription
 */
class BillingEvent extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'fx_rate_snapshot',
        'amount_base',
        'base_currency',
        'billing_cycle',
        'billed_at',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'subscription_id' => 'integer',
            'amount' => 'decimal:2',
            'fx_rate_snapshot' => 'decimal:6',
            'amount_base' => 'decimal:2',
            'billed_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
