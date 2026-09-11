<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\ProductSize;
use App\Models\AddOn;
use Illuminate\Http\Request;

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
        $sizes = Size::ordered()->get();
        return view('products.create', compact('categories', 'sizes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'sizes' => 'required|array|min:1',
            'sizes.*.size_id' => 'required|exists:sizes,id',
            'sizes.*.price' => 'required|numeric|min:0',
        ]);

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
        $sizes = Size::ordered()->get();
        return view('products.edit', compact('product', 'categories', 'sizes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sizes' => 'required|array|min:1',
            'sizes.*.size_id' => 'required|exists:sizes,id',
            'sizes.*.price' => 'required|numeric|min:0',
        ]);

        $product->update($request->only('name', 'category_id', 'description', 'is_active'));

        // Sync sizes
        $product->productSizes()->delete();
        foreach ($request->sizes as $sizeData) {
            ProductSize::create([
                'product_id' => $product->id,
                'size_id' => $sizeData['size_id'],
                'price' => $sizeData['price'],
            ]);
        }

        AuditLog::log('updated', 'products', "Product '{$product->name}' updated", null, 'product', $product->id);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'activated' : 'deactivated';

        AuditLog::log($status, 'products', "Product '{$product->name}' {$status}", null, 'product', $product->id);

        return back()->with('success', "Product {$status} successfully.");
    }
}
