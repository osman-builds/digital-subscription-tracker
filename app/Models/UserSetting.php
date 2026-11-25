<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'base_currency',
        'monthly_budget',
        'budget_alert_percent',
        'alert_7_days',
        'alert_3_days',
        'dark_mode',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'budget_alert_percent' => 'integer',
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
                'monthly_budget' => null,
                'budget_alert_percent' => 80,
                'alert_7_days' => true,
                'alert_3_days' => true,
                'dark_mode' => false,
            ]
        );
    }
}
