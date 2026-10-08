<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CategorySizeRule;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Recipe;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'sizes')->latest();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $products = $query->paginate(15);
        $categories = Category::ordered()->get();
        $sizes = Size::ordered()->get();

        return view('products.index', compact('products', 'categories', 'sizes'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $sizes = Size::orderBy('sort_order')->orderBy('name')->get();
        $sizeRules = $this->categorySizeOptions($categories);

        return view('products.create', compact('categories', 'sizes', 'sizeRules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'sizes' => 'required|array|min:1',
            'sizes.*.size_id' => 'required|exists:sizes,id|distinct',
            'sizes.*.price' => 'required|numeric|gt:0',
        ]);

        $this->validateCategorySizes((int) $request->category_id, $request->input('sizes', []));
        $product = Product::create($request->only('name', 'category_id', 'description'));

        foreach ($request->sizes as $sizeData) {
            ProductSize::create([
                'product_id' => $product->id,
                'size_id' => $sizeData['size_id'],
                'price' => $sizeData['price'],
            ]);
        }

        AuditLog::log('created', 'products', "Product '{$product->name}' created", null, 'product', $product->id);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('productSizes');
        $categories = Category::active()->ordered()->get();
        $sizes = Size::orderBy('sort_order')->orderBy('name')->get();
        $sizeRules = $this->categorySizeOptions($categories);
        $recipeSizeIds = Recipe::where('product_id', $product->id)->pluck('size_id')->map(fn ($id) => (int) $id);

        return view('products.edit', compact('product', 'categories', 'sizes', 'sizeRules', 'recipeSizeIds'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sizes' => 'required|array|min:1',
            'sizes.*.size_id' => 'required|exists:sizes,id|distinct',
            'sizes.*.price' => 'required|numeric|gt:0',
        ]);

        $sizeRows = $request->input('sizes', []);
        $this->validateCategorySizes((int) $request->category_id, $sizeRows, $product);

        $product->update($request->only('name', 'category_id', 'description', 'is_active'));

        $submittedSizeIds = [];
        foreach ($sizeRows as $sizeData) {
            $submittedSizeIds[] = (int) $sizeData['size_id'];
            ProductSize::updateOrCreate(
                ['product_id' => $product->id, 'size_id' => $sizeData['size_id']],
                ['price' => $sizeData['price']]
            );
        }

        $product->productSizes()->whereNotIn('size_id', $submittedSizeIds)->delete();

        AuditLog::log('updated', 'products', "Product '{$product->name}' updated", null, 'product', $product->id);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    private function categorySizeOptions($categories): array
    {
        return CategorySizeRule::query()
            ->with('size')
            ->where('is_active', true)
            ->whereIn('category_id', $categories->pluck('id'))
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rules) => $rules->map(fn ($rule) => [
                'id' => $rule->size_id,
                'name' => $rule->size->name,
                'sort_order' => $rule->sort_order,
            ])->values())
            ->all();
    }

    private function validateCategorySizes(int $categoryId, array $sizeRows, ?Product $product = null): void
    {
        $sizeIds = array_map(static fn ($row) => (int) ($row['size_id'] ?? 0), $sizeRows);
        if ($sizeIds === [] || count($sizeIds) !== count(array_unique($sizeIds))) {
            throw ValidationException::withMessages([
                'sizes' => 'Select at least one unique size.',
            ]);
        }

        $allowedIds = CategorySizeRule::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->pluck('size_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if ($product) {
            $retiredExistingIds = $product->productSizes()
                ->whereHas('size', fn ($query) => $query->where('is_active', false))
                ->pluck('size_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
            $allowedIds = array_values(array_unique(array_merge($allowedIds, $retiredExistingIds)));
        }

        $hasRules = CategorySizeRule::where('category_id', $categoryId)->where('is_active', true)->exists();
        if (! $hasRules) {
            $allowedIds = array_values(array_unique(array_merge(
                $allowedIds,
                Size::where('is_active', true)->pluck('id')->map(static fn ($id) => (int) $id)->all()
            )));
        }

        if (array_diff($sizeIds, $allowedIds) !== []) {
            throw ValidationException::withMessages([
                'sizes' => 'One or more selected sizes are not allowed for this category.',
            ]);
        }
    }
}
