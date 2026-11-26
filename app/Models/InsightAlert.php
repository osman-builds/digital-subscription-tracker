<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $subscription_id
 * @property string $alert_type
 * @property string $severity
 * @property string $title
 * @property string $message
 * @property string $dedupe_key
 * @property bool $is_read
 * @property Carbon|null $dismissed_at
 * @property Carbon|null $emailed_at
 * @property-read User|null $user
 * @property-read Subscription|null $subscription
 */
class InsightAlert extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'alert_type',
        'severity',
        'title',
        'message',
        'dedupe_key',
        'is_read',
        'dismissed_at',
        'emailed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'subscription_id' => 'integer',
            'is_read' => 'boolean',
            'dismissed_at' => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    /**
     * Prune read or dismissed alerts older than 90 days to protect VM disk storage.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(90))
            ->where(function (Builder $query): void {
                $query->where('is_read', true)
                    ->orWhereNotNull('dismissed_at');
            });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('dismissed_at');
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
