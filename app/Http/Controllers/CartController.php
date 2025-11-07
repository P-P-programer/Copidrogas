<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = auth()->user()->load('cartItems.product')->cartItems;
        
        $cart = [
            'items' => $items->map(fn($ci) => [
                'id' => $ci->product_id,
                'name' => $ci->product->name,
                'price' => (float)$ci->product->price,
                'image' => $ci->product->image,
                'qty' => $ci->qty,
            ])->toArray(),
            'count' => $items->sum('qty'),
            'total' => $items->sum(fn($ci) => $ci->qty * $ci->product->price),
        ];

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'qty' => ['required','integer','min:1','max:1000'],
        ]);

        $cartItem = CartItem::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $validated['product_id']],
            ['qty' => \DB::raw("qty + {$validated['qty']}")]
        );

        $cart = $this->getCartData();

        return response()->json(['ok' => true, 'cart' => $cart]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'qty' => ['required','integer','min:1','max:1000'],
        ]);

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$cartItem) {
            return response()->json(['ok' => false, 'message' => 'Producto no está en el carrito'], 404);
        }

        $cartItem->update(['qty' => $validated['qty']]);

        $cart = $this->getCartData();

        return response()->json(['ok' => true, 'cart' => $cart]);
    }

    public function remove(Product $product)
    {
        CartItem::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->delete();

        $cart = $this->getCartData();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true, 'cart' => $cart]);
        }

        return back()->with('success', 'Producto eliminado del carrito');
    }

    private function getCartData()
    {
        $items = auth()->user()->load('cartItems.product')->cartItems;

        return [
            'count' => $items->sum('qty'),
            'total' => $items->sum(fn($ci) => $ci->qty * $ci->product->price),
            'items' => $items->map(fn($ci) => [
                'id' => $ci->product_id,
                'name' => $ci->product->name,
                'price' => (float)$ci->product->price,
                'image' => $ci->product->image,
                'qty' => $ci->qty,
            ])->toArray(),
        ];
    }
}