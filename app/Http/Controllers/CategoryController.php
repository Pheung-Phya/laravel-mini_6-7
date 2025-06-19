<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::paginate(10);
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
        }

        $categories = Category::create($validated);
        return response()->json($categories);

    }

    /**
     * Display the specified resource.
     */
    public function getProductsByCategoryId($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'category' => $category->name,
            'products' => $category->products
        ]);
    }


    public function getProductsByCategorySlug($slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $products = $category->products()->get();

        return response()->json([
            'category' => $category->name,
            'products' => $products
        ]);
    }


    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        if (!$keyword) {
            return response()->json(['message' => 'Keyword is required'], 400);
        }

        // Get matching categories (without pagination for simplicity)
        $categories = Category::where('name', 'like', "%$keyword%")
            ->orWhere('slug', 'like', "%$keyword%")
            ->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No categories found'], 404);
        }

        // Collect all product IDs from matching categories
        $categoryIds = $categories->pluck('id');

        // Get all products belonging to those categories via pivot table
        $products = Product::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->get();

        return response()->json([
            'categories' => $categories,
            'products' => $products,
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
