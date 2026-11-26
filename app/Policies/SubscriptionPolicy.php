<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return (int) $subscription->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return (int) $subscription->user_id === (int) $user->id;
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return (int) $subscription->user_id === (int) $user->id;
    }
}
