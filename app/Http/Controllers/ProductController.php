<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | 1. Get All Categories With Products
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $categories = Category::with([
            'products:id,
            category_id,
            name,
            description,
            price,
            image'
        ])->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | 2. Get Products By Category
    |--------------------------------------------------------------------------
    */
    public function getProductByCate($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $products = $category->products()->get();

        return response()->json([
            'success' => true,
            'category' => $category->name,
            'products' => $products
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | 3. Search Products
    |--------------------------------------------------------------------------
    */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|max:255',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $query = Product::query();

        // Search by name
        $query->where('name', 'like', '%' . $validated['search'] . '%');

        // Filter by price
        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        // Filter by category
        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | 4. Store New Product
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;

        // Upload Image
        if ($request->hasFile('image')) {
            $imagePath = Storage::disk('public')
                ->putFile('products', $request->file('image'));
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | 5. Delete Product
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
