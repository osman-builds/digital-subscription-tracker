<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Support\Collection;

/**
 * Handles session-backed subscriptions for isolated sandbox sessions.
 */
class SandboxSubscriptionStore
{
    public function __construct(
        private SandboxService $sandbox,
        private SandboxCategoryStore $categories,
    ) {}

    /** @return Collection<int, Subscription> */
    public function all(int $userId): Collection
    {
        $this->sandbox->ensureInitialized($userId);
        $categoriesById = $this->categories->all($userId)->keyBy('id');
        $items = (array) session(SandboxService::KEY_SUBSCRIPTIONS, []);

        return collect($items)
            ->map(fn (array $item): Subscription => $this->hydrate($item, $categoriesById))
            ->values();
    }

    /** @return Collection<int, Subscription> */
    public function active(int $userId): Collection
    {
        return $this->all($userId)->where('status', 'active')->values();
    }

    /** @return Collection<int, Subscription> */
    public function dueSoon(int $userId, int $days = 7): Collection
    {
        $start = now()->startOfDay();
        $end = now()->addDays($days)->endOfDay();

        return $this->active($userId)
            ->filter(fn (Subscription $sub): bool => $sub->next_billing_date->between($start, $end))
            ->sortBy('next_billing_date')
            ->values();
    }

    public function findOrFail(int $userId, int $subscriptionId): Subscription
    {
        $subscription = $this->all($userId)->firstWhere('id', $subscriptionId);
        abort_if($subscription === null, 404);

        return $subscription;
    }

    public function create(int $userId, array $attributes): Subscription
    {
        $this->sandbox->ensureInitialized($userId);
        $nextId = $this->sandbox->allocateId();

        $item = [
            'id' => $nextId,
            'user_id' => $userId,
            'category_id' => $attributes['category_id'] ?? null,
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'amount' => (float) $attributes['amount'],
            'currency' => strtoupper((string) $attributes['currency']),
            'amount_myr' => (float) $attributes['amount_myr'],
            'billing_cycle' => $attributes['billing_cycle'],
            'start_date' => $attributes['start_date'],
            'next_billing_date' => $attributes['next_billing_date'],
            'status' => $attributes['status'],
            'website_url' => $attributes['website_url'] ?? null,
            'logo_url' => $attributes['logo_url'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ];

        $subscriptions = (array) session(SandboxService::KEY_SUBSCRIPTIONS, []);
        $subscriptions[$nextId] = $item;
        session([SandboxService::KEY_SUBSCRIPTIONS => $subscriptions]);

        return $this->findOrFail($userId, $nextId);
    }

    public function update(int $userId, Subscription $subscription, array $attributes): void
    {
        $this->sandbox->ensureInitialized($userId);
        $subscriptions = (array) session(SandboxService::KEY_SUBSCRIPTIONS, []);
        $id = (int) $subscription->id;

        abort_if(! isset($subscriptions[$id]), 404);

        $subscriptions[$id] = array_merge($subscriptions[$id], $attributes);
        session([SandboxService::KEY_SUBSCRIPTIONS => $subscriptions]);
    }

    public function delete(int $userId, Subscription $subscription): void
    {
        $this->sandbox->ensureInitialized($userId);
        $subscriptions = (array) session(SandboxService::KEY_SUBSCRIPTIONS, []);
        unset($subscriptions[(int) $subscription->id]);
        session([SandboxService::KEY_SUBSCRIPTIONS => $subscriptions]);
    }

    /** @param Collection<int|string, Category> $categoriesById */
    private function hydrate(array $item, Collection $categoriesById): Subscription
    {
        $subscription = new Subscription;
        $subscription->forceFill($item);

        $catId = $item['category_id'] ?? null;
        if ($catId !== null && $categoriesById->has((int) $catId)) {
            $subscription->setRelation('category', $categoriesById->get((int) $catId));
        }

        return $subscription;
    }
}
