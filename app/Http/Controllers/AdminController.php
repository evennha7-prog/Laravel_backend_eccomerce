<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
            ],
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'PENDING')->count(),
                'completed' => Order::where('status', 'COMPLETED')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ], 200);
    }

    public function getUsers(): JsonResponse
    {
        $users = User::select(['id', 'username', 'email', 'role', 'status', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
        ], 200);
    }

    public function getUser(int $id): JsonResponse
    {
        $user = User::select(['id', 'username', 'email', 'role', 'status', 'avatar', 'created_at'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => $user,
        ], 200);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255',
            'avatar'=>'default'
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'status' => 'sometimes|in:active,inactive',
            'role' => 'sometimes|in:admin,user',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    public function deleteUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ], 200);
    }

    public function toggleUserStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        if ($user->status === 'inactive') {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'User status updated',
            'status' => $user->status,
        ], 200);
    }

    public function getProducts(): JsonResponse
    {
        $products = Product::with('category:id,name')
            ->select(['id', 'category_id', 'name', 'description', 'price', 'image', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
        ], 200);
    }

    public function createProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
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
            'product' => $product,
        ], 201);
    }

    public function updateProduct(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function deleteProduct(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ], 200);
    }

    public function getCategories(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ], 200);
    }

    public function createCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,'.$id,
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'category' => $category,
        ], 200);
    }

    public function deleteCategory(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ], 200);
    }

    public function getOrders(): JsonResponse
    {
        $orders = Order::with(['user:id,username,email', 'items.product', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ], 200);
    }

    public function getOrder(int $id): JsonResponse
    {
        $order = Order::with(['user:id,username,email', 'items.product', 'address'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order,
        ], 200);
    }

    public function updateOrderStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:PENDING,PROCESSING,SHIPPED,COMPLETED,CANCELLED',
        ]);

        $order->status = $validated['status'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'order' => $order,
        ], 200);
    }

    public function deleteOrder(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->items()->delete();
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
        ], 200);
    }

    public function getAddresses(): JsonResponse
    {
        $addresses = Address::with('user:id,username,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses,
        ], 200);
    }

    public function getAddress(int $id): JsonResponse
    {
        $address = Address::with('user:id,username,email')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'address' => $address,
        ], 200);
    }

    public function deleteAddress(int $id): JsonResponse
    {
        $address = Address::findOrFail($id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ], 200);
    }
}
