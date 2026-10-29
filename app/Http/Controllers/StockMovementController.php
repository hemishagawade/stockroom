<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(): View
    {
        return view('stock-movements.index', [
            'movements' => StockMovement::with('material.category')
                ->latest('movement_date')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('stock-movements.create', [
            'categories' => Category::with(['materials' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'movement' => new StockMovement(['movement_date' => today()]),
            'editing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'material_id' => [
                'required',
                'integer',
                Rule::exists('materials', 'id')->where('category_id', $request->input('category_id')),
            ],
            'movement_date' => ['required', 'date_format:Y-m-d'],
            'quantity' => ['required', 'numeric', 'max:9999999999.99', 'min:-9999999999.99', 'decimal:0,2'],
        ], $this->validationMessages());
        unset($validated['category_id']);

        StockMovement::create($validated);

        return redirect()->route('stock-movements.index')->with('success', 'Stock movement recorded successfully.');
    }

    public function edit(StockMovement $stockMovement): View
    {
        abort_if($stockMovement->material?->trashed(), 404);

        return view('stock-movements.create', [
            'categories' => Category::with(['materials' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'movement' => $stockMovement,
            'editing' => true,
        ]);
    }

    public function update(Request $request, StockMovement $stockMovement): RedirectResponse
    {
        abort_if($stockMovement->material?->trashed(), 404);
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'material_id' => [
                'required',
                'integer',
                Rule::exists('materials', 'id')->where('category_id', $request->input('category_id')),
            ],
            'movement_date' => ['required', 'date_format:Y-m-d'],
            'quantity' => ['required', 'numeric', 'max:9999999999.99', 'min:-9999999999.99', 'decimal:0,2'],
        ], $this->validationMessages());
        unset($validated['category_id']);

        $stockMovement->update($validated);

        return redirect()->route('stock-movements.index')->with('success', 'Stock movement updated successfully.');
    }

    public function destroy(StockMovement $stockMovement): RedirectResponse
    {
        $stockMovement->delete();

        return redirect()->route('stock-movements.index')->with('success', 'Stock movement deleted and balance recalculated.');
    }

    private function validationMessages(): array
    {
        return [
            'category_id.required' => 'Choose a material category.',
            'category_id.exists' => 'Choose a category that exists.',
            'material_id.required' => 'Choose a material.',
            'material_id.exists' => 'Choose a material in the selected category.',
            'movement_date.required' => 'Choose the movement date.',
            'movement_date.date_format' => 'Enter a valid movement date.',
            'quantity.required' => 'Enter an inward or outward quantity.',
            'quantity.numeric' => 'Enter a valid quantity.',
            'quantity.max' => 'Quantity is too large.',
            'quantity.min' => 'Quantity is too small.',
            'quantity.decimal' => 'Quantity can have no more than 2 decimal places.',
        ];
    }
}
