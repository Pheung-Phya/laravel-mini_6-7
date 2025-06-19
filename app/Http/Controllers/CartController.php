<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cart = Cart::with('cartItem.product')->where('user_id', $request->user()->id)->first();
        return response()->json($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $item = $cart->cartItem()->where('product_id', $request->product_id)->first();

        if ($item) {
        // Increase quantity
            $item->increment('quantity', $request->quantity ?? 1);
        } else {
            // Add new item
            $cart->cartItem()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1
            ]);
        }
        return response()->json(['message' => 'Added to cart']);

    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::findOrFail($id);

        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Cart item updated']);
    }

    public function destroy(Request $request,string $id)
    {

        $cartItem = CartItem::findOrFail($id);

        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Cart item removed']);
    }
}