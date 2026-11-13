<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\SandboxCategoryStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    private const COLOR_HEX_REGEX = 'regex:/^#[0-9A-Fa-f]{6}$/';

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

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $userId = $user->id;

        if ($user->isSandbox()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'color' => ['required', 'string', 'size:7', self::COLOR_HEX_REGEX],
            ]);

            if ($this->sandboxCategories->nameExists($userId, $validated['name'])) {
                return back()->withErrors(['name' => 'A category with this name already exists.'])->withInput();
            }

            $this->sandboxCategories->create($userId, $validated);

            return redirect()->route('categories.index')
                ->with('success', "Category \"{$validated['name']}\" created (sandbox session only)!");
        }

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
            'color' => ['required', 'string', 'size:7', self::COLOR_HEX_REGEX],
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

        $user = auth()->user();
        $userId = $user->id;

        if ($user->isSandbox()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'color' => ['required', 'string', 'size:7', self::COLOR_HEX_REGEX],
            ]);

            if ($this->sandboxCategories->nameExists($userId, $validated['name'], (int) $category->id)) {
                return back()->withErrors(['name' => 'A category with this name already exists.'])->withInput();
            }

            $this->sandboxCategories->update($userId, $category, $validated);

            return redirect()->route('categories.index')
                ->with('success', 'Category updated (sandbox session only)!');
        }

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
            'color' => ['required', 'string', 'size:7', self::COLOR_HEX_REGEX],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_if($category->isSystem(), 403, 'System categories cannot be deleted.');
        abort_if($category->user_id !== auth()->id(), 403);

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
