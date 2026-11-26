<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subscription_id
 * @property numeric-string|float $old_amount
 * @property numeric-string|float $new_amount
 * @property string $currency
 * @property numeric-string|float $change_amount
 * @property numeric-string|float $change_percent
 * @property string $detected_via
 * @property Carbon|null $recorded_at
 * @property-read Subscription|null $subscription
 */
class SubscriptionPriceHistory extends Model
{
    protected $fillable = [
        'subscription_id',
        'old_amount',
        'new_amount',
        'currency',
        'change_amount',
        'change_percent',
        'detected_via',
        'recorded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subscription_id' => 'integer',
            'old_amount' => 'decimal:2',
            'new_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'change_percent' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
