<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Size;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with('product.category', 'size', 'recipeIngredients.ingredient');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $recipes = $query->get()->groupBy('product_id');
        $products = Product::with('category')->orderBy('name')->get();

        return view('recipes.index', compact('recipes', 'products'));
    }

    public function create()
    {
        $products = Product::active()->with('sizes')->orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();
        $sizes = Size::ordered()->get();
        return view('recipes.create', compact('products', 'ingredients', 'sizes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_id' => 'required|exists:sizes,id',
            'notes' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Check for existing recipe
        if (Recipe::where('product_id', $request->product_id)->where('size_id', $request->size_id)->exists()) {
            return back()->with('error', 'A recipe already exists for this product and size combination.')->withInput();
        }

        $recipe = Recipe::create($request->only('product_id', 'size_id', 'notes'));

        foreach ($request->ingredients as $ing) {
            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing['ingredient_id'],
                'quantity' => $ing['quantity'],
            ]);
        }

        $product = Product::find($request->product_id);
        $size = Size::find($request->size_id);
        AuditLog::log('created', 'recipes', "Recipe created for {$product->name} ({$size->name})", null, 'recipe', $recipe->id);

        return redirect()->route('recipes.index')->with('success', 'Recipe created successfully.');
    }

    public function edit(Recipe $recipe)
    {
        $recipe->load('recipeIngredients.ingredient', 'product', 'size');
        $ingredients = Ingredient::orderBy('name')->get();
        return view('recipes.edit', compact('recipe', 'ingredients'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $recipe->update($request->only('notes'));

        // Sync ingredients
        $recipe->recipeIngredients()->delete();
        foreach ($request->ingredients as $ing) {
            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing['ingredient_id'],
                'quantity' => $ing['quantity'],
            ]);
        }

        AuditLog::log('updated', 'recipes', "Recipe updated for {$recipe->product->name} ({$recipe->size->name})", null, 'recipe', $recipe->id);

        return redirect()->route('recipes.index')->with('success', 'Recipe updated successfully.');
    }

    public function destroy(Recipe $recipe)
    {
        $productName = $recipe->product->name;
        $sizeName = $recipe->size->name;
        $recipe->delete();

        AuditLog::log('deleted', 'recipes', "Recipe deleted for {$productName} ({$sizeName})");

        return redirect()->route('recipes.index')->with('success', 'Recipe deleted successfully.');
    }
}
