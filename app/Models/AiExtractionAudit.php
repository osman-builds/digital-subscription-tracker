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
 * @property string $input_type
 * @property string $file_sha256
 * @property string $model_used
 * @property numeric-string|float $confidence_score
 * @property string $status
 * @property Carbon|null $verified_at
 * @property-read User|null $user
 * @property-read Subscription|null $subscription
 */
class AiExtractionAudit extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'input_type',
        'file_sha256',
        'model_used',
        'confidence_score',
        'status',
        'verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'subscription_id' => 'integer',
            'confidence_score' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Prune non-file governance logs older than 90 days to protect VM storage.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(90));
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
