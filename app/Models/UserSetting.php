<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'base_currency',
        'alert_7_days',
        'alert_3_days',
        'dark_mode',
    ];

    protected $casts = [
        'alert_7_days' => 'boolean',
        'alert_3_days' => 'boolean',
        'dark_mode' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create settings for the given user ID.
     */
    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'base_currency' => 'MYR',
                'alert_7_days' => true,
                'alert_3_days' => true,
                'dark_mode' => false,
            ]
        );
    }
}
