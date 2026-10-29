<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('materials')
            ->orderBy('name')
            ->paginate(12);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create', [
            'category' => new Category,
            'existingNames' => Category::query()->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:categories,name'],
        ], [
            'name.required' => 'Enter a category name.',
            'name.max' => 'Category names may not exceed 60 characters.',
            'name.unique' => 'A category with this name already exists.',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', [
            'category' => $category,
            'existingNames' => Category::query()->where('id', '<>', $category->id)->pluck('name'),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('categories', 'name')->ignore($category->id)],
        ], [
            'name.required' => 'Enter a category name.',
            'name.max' => 'Category names may not exceed 60 characters.',
            'name.unique' => 'A category with this name already exists.',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if (Material::withTrashed()->where('category_id', $category->id)->exists()) {
            return back()->with('error', 'This category is assigned to materials and cannot be deleted.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
