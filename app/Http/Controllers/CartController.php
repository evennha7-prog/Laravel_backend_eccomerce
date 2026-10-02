<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get the authenticated user's active cart.
     */
    public function getCarts(): JsonResponse
    {
        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->with('items.product')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No Cart Found',
                'cart' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'cart' => $cart,
        ], 200);
    }

    /**
     * Add a product to the active cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $product = Product::findOrFail($validated['product_id']);

        $cart = DB::transaction(function () use ($user, $product, $validated) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'status' => 'ACTIVE'],
                ['total' => 0]
            );

            $item = $cart->items()->where('product_id', $product->id)->first();

            if ($item) {
                $item->quantity += $validated['quantity'];
                $item->price = $item->quantity * $product->price;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'price' => $validated['quantity'] * $product->price,
                ]);
            }

            $cart->total = $cart->items()->sum('price');
            $cart->save();

            return $cart;
        });

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart' => $cart->load('items.product'),
        ], 200);
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function update(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        $cart = DB::transaction(function () use ($cart, $itemId, $validated) {
            $item = $cart->items()->with('product')->where('id', $itemId)->firstOrFail();

            $item->quantity = $validated['quantity'];
            $item->price = $item->quantity * $item->product->price;
            $item->save();

            $cart->total = $cart->items()->sum('price');
            $cart->save();

            return $cart;
        });

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'cart' => $cart->load('items.product'),
        ], 200);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(int $itemId): JsonResponse
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        $cart = DB::transaction(function () use ($cart, $itemId) {
            $item = $cart->items()->where('id', $itemId)->firstOrFail();
            $item->delete();

            $cart->total = $cart->items()->sum('price') ?: 0;
            $cart->save();

            return $cart;
        });

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'cart' => $cart->load('items.product'),
        ], 200);
    }

    /**
     * Clear all items from the active cart.
     */
    public function clear(): JsonResponse
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($cart) {
            DB::transaction(function () use ($cart) {
                $cart->items()->delete();
                $cart->total = 0;
                $cart->save();
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cart' => $cart ? $cart->load('items') : null,
        ], 200);
    }
}
