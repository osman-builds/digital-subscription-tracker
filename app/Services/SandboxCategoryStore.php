<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Handles session-backed custom categories for isolated sandbox sessions.
 */
class SandboxCategoryStore
{
    public function __construct(private SandboxService $sandbox) {}

    /** @return Collection<int, Category> */
    public function all(int $userId): Collection
    {
        $this->sandbox->ensureInitialized($userId);

        $systemCategories = Category::whereNull('user_id')->orderBy('name')->get();
        $sessionItems = (array) session(SandboxService::KEY_CATEGORIES, []);
        $subscriptions = (array) session(SandboxService::KEY_SUBSCRIPTIONS, []);

        $counts = [];
        foreach ($subscriptions as $sub) {
            $catId = $sub['category_id'] ?? null;
            if ($catId !== null) {
                $counts[(int) $catId] = ($counts[(int) $catId] ?? 0) + 1;
            }
        }

        $customCategories = collect($sessionItems)
            ->sortBy('name')
            ->map(fn (array $item): Category => $this->hydrate($item, $userId));

        return $systemCategories->concat($customCategories)->each(function (Category $cat) use ($counts): void {
            $cat->setAttribute('subscriptions_count', $counts[(int) $cat->id] ?? 0);
        })->values();
    }

    public function nameExists(int $userId, string $name, ?int $ignoreId = null): bool
    {
        $normalized = strtolower(trim($name));

        return $this->all($userId)->contains(function (Category $cat) use ($normalized, $ignoreId): bool {
            if ($ignoreId !== null && (int) $cat->id === $ignoreId) {
                return false;
            }

            return strtolower($cat->name) === $normalized;
        });
    }

    public function findOrFail(int $userId, int $categoryId): Category
    {
        $category = $this->all($userId)->firstWhere('id', $categoryId);
        abort_if($category === null, 404);

        return $category;
    }

    public function create(int $userId, array $attributes): Category
    {
        $this->sandbox->ensureInitialized($userId);
        $nextId = $this->sandbox->allocateId();

        $item = [
            'id' => $nextId,
            'user_id' => $userId,
            'name' => $attributes['name'],
            'color' => $attributes['color'],
        ];

        $categories = (array) session(SandboxService::KEY_CATEGORIES, []);
        $categories[$nextId] = $item;
        session([SandboxService::KEY_CATEGORIES => $categories]);

        return $this->hydrate($item, $userId);
    }

    public function update(int $userId, Category $category, array $attributes): void
    {
        $this->sandbox->ensureInitialized($userId);
        $categories = (array) session(SandboxService::KEY_CATEGORIES, []);
        $id = (int) $category->id;

        abort_if(! isset($categories[$id]), 404);

        $categories[$id]['name'] = $attributes['name'];
        $categories[$id]['color'] = $attributes['color'];
        session([SandboxService::KEY_CATEGORIES => $categories]);
    }

    public function delete(int $userId, Category $category): void
    {
        $this->sandbox->ensureInitialized($userId);
        $id = (int) $category->id;

        $categories = (array) session(SandboxService::KEY_CATEGORIES, []);
        unset($categories[$id]);

        $subscriptions = (array) session(SandboxService::KEY_SUBSCRIPTIONS, []);
        foreach ($subscriptions as $subId => $sub) {
            if ((int) ($sub['category_id'] ?? 0) === $id) {
                $subscriptions[$subId]['category_id'] = null;
            }
        }

        session([
            SandboxService::KEY_CATEGORIES => $categories,
            SandboxService::KEY_SUBSCRIPTIONS => $subscriptions,
        ]);
    }

    private function hydrate(array $item, int $userId): Category
    {
        $category = new Category;
        $category->forceFill([
            'id' => (int) $item['id'],
            'user_id' => $userId,
            'name' => $item['name'],
            'color' => $item['color'],
        ]);

        return $category;
    }
}
