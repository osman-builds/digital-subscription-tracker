<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
