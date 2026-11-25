<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
