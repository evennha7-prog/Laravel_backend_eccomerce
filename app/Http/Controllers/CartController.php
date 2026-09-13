<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Apply Sanctum auth
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET /api/cart - view user's active cart
    public function getCarts()
    {
        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'No Cart Found',
                'cart' => null
            ], 200);
        }

        return response()->json([
            'cart' => $cart
        ], 200);
    }

    // POST /api/cart - add product to cart
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $product = Product::findOrFail($request->product_id);

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'ACTIVE'],
            ['total' => 0]
        );

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->price = $item->quantity * $product->price;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'price'      => $request->quantity * $product->price,
            ]);
        }

        $cart->total = $cart->items()->sum('price');
        $cart->save();

        return response()->json([
            'message' => 'Product added to cart successfully',
            'cart' => $cart->load('items.product')
        ], 200);
    }

    // PUT /api/cart/{itemId} - update item quantity
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        $item = $cart->items()->with('product')->where('id', $itemId)->firstOrFail();

        $item->quantity = $request->quantity;
        $item->price = $item->quantity * $item->product->price;
        $item->save();

        $cart->total = $cart->items()->sum('price');
        $cart->save();

        return response()->json([
            'message' => 'Cart item updated successfully',
            'cart' => $cart->load('items.product')
        ], 200);
    }

    // DELETE /api/cart/{itemId} - remove item
    public function destroy($itemId)
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        $item = $cart->items()->where('id', $itemId)->firstOrFail();
        $item->delete();

        $cart->total = $cart->items()->sum('price');
        $cart->save();

        return response()->json([
            'message' => 'Item removed from cart successfully',
            'cart' => $cart->load('items.product')
        ], 200);
    }

    // DELETE /api/cart/clear - clear entire cart
    public function clear()
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->total = 0;
            $cart->save();
        }

        return response()->json([
            'message' => 'Cart cleared successfully',
            'cart' => $cart
        ], 200);
    }
}
