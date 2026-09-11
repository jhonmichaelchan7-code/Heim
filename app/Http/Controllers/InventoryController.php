<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::query()->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'low_stock' => $query->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0),
                'out_of_stock' => $query->where('current_stock', '<=', 0),
                'good' => $query->whereColumn('current_stock', '>', 'minimum_stock'),
                default => null,
            };
        }

        $ingredients = $query->paginate(20);

        return view('inventory.index', compact('ingredients'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|in:g,ml,pcs,oz,kg,L',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
        ]);

        Ingredient::create($request->only('name', 'unit', 'current_stock', 'minimum_stock'));

        return redirect()->route('inventory.index')->with('success', 'Ingredient added successfully.');
    }

    public function edit(Ingredient $ingredient)
    {
        return view('inventory.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|in:g,ml,pcs,oz,kg,L',
            'minimum_stock' => 'required|numeric|min:0',
        ]);

        $ingredient->update($request->only('name', 'unit', 'minimum_stock'));

        return redirect()->route('inventory.index')->with('success', 'Ingredient updated successfully.');
    }

    public function stockIn(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $ingredient = Ingredient::findOrFail($request->ingredient_id);
        $inventoryService->stockIn($ingredient, $request->quantity, $request->supplier, $request->notes);

        return back()->with('success', "Stock in: {$request->quantity} {$ingredient->unit} of {$ingredient->name} added successfully.");
    }

    public function waste(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $ingredient = Ingredient::findOrFail($request->ingredient_id);
        $inventoryService->recordWaste($ingredient, $request->quantity, $request->reason, $request->notes);

        return back()->with('success', "Waste recorded: {$request->quantity} {$ingredient->unit} of {$ingredient->name}.");
    }

    public function adjust(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $ingredient = Ingredient::findOrFail($request->ingredient_id);
        $inventoryService->adjustStock($ingredient, $request->quantity, $request->reason, $request->notes);

        return back()->with('success', "Stock adjusted for {$ingredient->name}.");
    }

    public function transactions(Request $request)
    {
        $query = InventoryTransaction::with('ingredient', 'performer')->latest();

        if ($request->filled('ingredient_id')) {
            $query->where('ingredient_id', $request->ingredient_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(25);
        $ingredients = Ingredient::orderBy('name')->get();

        return view('inventory.transactions', compact('transactions', 'ingredients'));
    }

    public function stockInForm()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('inventory.stock-in', compact('ingredients'));
    }

    public function wasteForm()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('inventory.waste', compact('ingredients'));
    }
}
