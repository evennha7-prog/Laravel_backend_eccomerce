<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Checkout the user's active cart into an order.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        $user = auth()->user();

        // Ensure the address belongs to the authenticated user
        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $address) {
            return response()->json([
                'success' => false,
                'message' => 'The selected address does not belong to you or does not exist.',
            ], 422);
        }

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->with('items.product')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active cart found to checkout',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $totalAmount = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $validated['address_id'],
                'status' => 'PENDING',
                'total' => $totalAmount,
                'cart_id' => $cart->id,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // Set cart status to INACTIVE (enum is ['ACTIVE', 'INACTIVE'])
            $cart->update(['status' => 'INACTIVE']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order->load(['items.product', 'address']),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all orders for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ], 200);
    }
}
