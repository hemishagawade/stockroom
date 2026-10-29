<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $archived = $request->string('status')->value() === 'archived';
        $materials = Material::query()
            ->with('category')
            ->withSum('stockMovements as movements_total', 'quantity')
            ->when($archived, fn ($query) => $query->onlyTrashed())
            ->when(! $archived, fn ($query) => $query->whereNull('deleted_at'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $activeMaterials = Material::query()
            ->withSum('stockMovements as movements_total', 'quantity')
            ->get();
        $totalOnHand = $activeMaterials->sum(
            fn (Material $material) => (float) $material->opening_balance + (float) ($material->movements_total ?? 0)
        );

        return view('materials.index', [
            'materials' => $materials,
            'activeMaterialsCount' => Material::count(),
            'totalOnHand' => $totalOnHand,
            'archived' => $archived,
        ]);
    }

    public function create(): View
    {
        return view('materials.create', [
            'categories' => Category::orderBy('name')->get(),
            'material' => new Material,
            'existingMaterials' => Material::withTrashed()->get(['category_id', 'name'])
                ->groupBy('category_id')
                ->map(fn ($materials) => $materials->pluck('name')->values()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9 ._-]*$/',
                Rule::unique('materials')->where('category_id', $request->input('category_id'))],
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ], [
            'category_id.required' => 'Choose a material category.',
            'category_id.exists' => 'Choose a category that exists.',
            'name.required' => 'Enter a material name.',
            'name.regex' => 'Use letters, numbers, spaces, hyphens, underscores or periods.',
            'name.unique' => 'A material with this name already exists in the selected category.',
            'opening_balance.required' => 'Enter an opening balance.',
            'opening_balance.numeric' => 'Enter a valid opening balance.',
            'opening_balance.min' => 'Opening balance cannot be negative.',
            'opening_balance.max' => 'Opening balance is too large.',
            'opening_balance.decimal' => 'Opening balance can have no more than 2 decimal places.',
        ]);

        Material::create($validated);

        return redirect()->route('materials.index')->with('success', 'Material added successfully.');
    }

    public function edit(Material $material): View
    {
        return view('materials.edit', [
            'categories' => Category::orderBy('name')->get(),
            'material' => $material,
            'existingMaterials' => Material::withTrashed()->where('id', '<>', $material->id)
                ->get(['category_id', 'name'])
                ->groupBy('category_id')
                ->map(fn ($materials) => $materials->pluck('name')->values()),
        ]);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9 ._-]*$/',
                Rule::unique('materials')->ignore($material->id)
                    ->where('category_id', $request->input('category_id'))],
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ], [
            'category_id.required' => 'Choose a material category.',
            'category_id.exists' => 'Choose a category that exists.',
            'name.required' => 'Enter a material name.',
            'name.regex' => 'Use letters, numbers, spaces, hyphens, underscores or periods.',
            'name.unique' => 'A material with this name already exists in the selected category.',
            'opening_balance.required' => 'Enter an opening balance.',
            'opening_balance.numeric' => 'Enter a valid opening balance.',
            'opening_balance.min' => 'Opening balance cannot be negative.',
            'opening_balance.max' => 'Opening balance is too large.',
            'opening_balance.decimal' => 'Opening balance can have no more than 2 decimal places.',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')->with('success', 'Material updated successfully.');
    }

    public function restore(Material $material): RedirectResponse
    {
        $material->restore();

        return redirect()->route('materials.index')->with('success', 'Material restored successfully.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Material archived. Stock history has been kept.');
    }
}
