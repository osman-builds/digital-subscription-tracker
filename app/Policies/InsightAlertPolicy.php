<?php

namespace App\Policies;

use App\Models\InsightAlert;
use App\Models\User;

class InsightAlertPolicy
{
    public function update(User $user, InsightAlert $insightAlert): bool
    {
        return (int) $insightAlert->user_id === (int) $user->id;
    }
}
