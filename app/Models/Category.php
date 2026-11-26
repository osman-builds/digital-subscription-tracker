<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $color
 * @property-read User|null $user
 */
class Category extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'color',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
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
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if the category is a system default.
     */
    public function isSystem(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Scope to categories accessible to the user (system defaults + their own).
     */
    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $query->where(function (Builder $builder) use ($userId) {
            $builder->whereNull('user_id');

            if ($userId !== null) {
                $builder->orWhere('user_id', $userId);
            }
        });
    }
}
