<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->ordered()->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category = Category::create($request->only('name', 'description', 'sort_order'));
        AuditLog::log('created', 'categories', "Category '{$category->name}' created", null, 'category', $category->id);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($request->only('name', 'description', 'sort_order', 'is_active'));
        AuditLog::log('updated', 'categories', "Category '{$category->name}' updated", null, 'category', $category->id);

        return back()->with('success', 'Category updated successfully.');
    }
}
