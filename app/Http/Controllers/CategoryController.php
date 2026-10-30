<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $categories = Category::forUser($userId)
            ->withCount(['subscriptions' => function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            }])
            ->orderByRaw('user_id IS NOT NULL')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) use ($userId) {
                    return $query->where(function ($q) use ($userId): void {
                        $q->whereNull('user_id')->orWhere('user_id', $userId);
                    });
                }),
            ],
            'color' => ['required', 'string', 'size:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $validated['user_id'] = $userId;

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$validated['name']}\" created!");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->isSystem(), 403, 'System categories cannot be modified.');
        abort_if($category->user_id !== auth()->id(), 403);

        $userId = auth()->id();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(function ($query) use ($userId) {
                        return $query->where(function ($q) use ($userId): void {
                            $q->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                    })
                    ->ignore($category->id),
            ],
            'color' => ['required', 'string', 'size:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_if($category->isSystem(), 403, 'System categories cannot be deleted.');
        abort_if($category->user_id !== auth()->id(), 403);

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted.');
    }
}
