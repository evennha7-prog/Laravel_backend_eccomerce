<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;   
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'address_id' => 'required|integer',
        ]);

        $user = auth()->user();


        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'No active cart found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $totalAmount = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = Order::create([
                'user_id'    => $user->id,
                'address_id' => $request->address_id,
                'status'     => 'PENDING',
                'total'      => $totalAmount,
                'cart_id'    => $cart->id,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);
            }

            $cart->update(['status' => 'COMPLETED']);


            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order'   => $order->load('items.product'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Checkout failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->get();

        return response()->json($orders, 200);
    }
}
