<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('subscriptions')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255|unique:categories,name',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$validated['name']}\" created!");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255|unique:categories,name,' . $category->id,
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', "Category updated!");
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', "Category deleted.");
    }
}
