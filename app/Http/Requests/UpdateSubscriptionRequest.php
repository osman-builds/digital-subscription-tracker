<?php

namespace App\Http\Requests;

use App\Models\Subscription;

class UpdateSubscriptionRequest extends StoreSubscriptionRequest
{
    public function authorize(): bool
    {
        $subscription = $this->route('subscription');

        if (! $subscription instanceof Subscription) {
            return false;
        }

        return $this->user()?->can('update', $subscription) ?? false;
    }
}
