<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\SandboxCategoryStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private SandboxCategoryStore $sandboxCategories) {}

    public function index(): View
    {
        $user = auth()->user();
        $userId = $user->id;

        if ($user->isSandbox()) {
            $categories = $this->sandboxCategories->all($userId);

            return view('categories.index', compact('categories'));
        }

        $categories = Category::forUser($userId)
            ->withCount(['subscriptions' => function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            }])
            ->orderByRaw('user_id IS NOT NULL')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->isSandbox()) {
            $this->sandboxCategories->create($user->id, $validated);

            return redirect()->route('categories.index')
                ->with('success', "Category \"{$validated['name']}\" created (sandbox session only)!");
        }

        $validated['user_id'] = $user->id;

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$validated['name']}\" created!");
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->isSandbox()) {
            $this->sandboxCategories->update($user->id, $category, $validated);

            return redirect()->route('categories.index')
                ->with('success', 'Category updated (sandbox session only)!');
        }

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $user = auth()->user();
        if ($user->isSandbox()) {
            $this->sandboxCategories->delete($user->id, $category);

            return redirect()->route('categories.index')
                ->with('success', 'Category deleted (sandbox session only).');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted.');
    }
}
