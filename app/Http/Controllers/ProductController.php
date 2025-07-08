<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductDetail;
use App\Http\Resources\ProductHome;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(){
        $p = new Product();
        $products = Product::paginate(10);

        return response()->json($products);
    }


    public function homeProduct()
    {
        $products = Product::paginate(10);

        return response()->json([
            'data' => ProductHome::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show($id){
        $product = Product::find($id);
            if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    return new ProductDetail($product);
    }


    public function store(Request $request){
       $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image'        => 'nullable',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/products', $filename);
            $validated['image'] = '/storage/products/' . $filename;
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product = Product::create($validated);

        if (!empty($validated['category_ids'])) {
            $product->category()->attach($validated['category_ids']);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product->load('category'),
        ], 201);
    }
}
