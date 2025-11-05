<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', ['items' => [], 'count' => 0, 'total' => 0]);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'qty' => ['required','integer','min:1','max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $cart = session('cart', ['items' => [], 'count' => 0, 'total' => 0]);
        $items = $cart['items'];

        if (isset($items[$product->id])) {
            $items[$product->id]['qty'] += $validated['qty'];
        } else {
            $items[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float)$product->price,
                'image' => $product->image,
                'qty' => $validated['qty'],
            ];
        }

        // Recalcular totales
        $count = 0; $total = 0;
        foreach ($items as $it) {
            $count += $it['qty'];
            $total += $it['price'] * $it['qty'];
        }
        $cart = ['items' => $items, 'count' => $count, 'total' => $total];
        session(['cart' => $cart]);

        return response()->json([
            'ok' => true,
            'cart' => [
                'count' => $count,
                'total' => $total,
                'items' => $items,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'qty' => ['required','integer','min:1','max:1000'],
        ]);

        $cart = session('cart', ['items' => [], 'count' => 0, 'total' => 0]);
        if (!isset($cart['items'][$validated['product_id']])) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Producto no está en el carrito'], 404);
            }
            return back()->with('error', 'Producto no está en el carrito');
        }

        $cart['items'][$validated['product_id']]['qty'] = $validated['qty'];

        $count = 0; $total = 0;
        foreach ($cart['items'] as $it) {
            $count += $it['qty'];
            $total += $it['price'] * $it['qty'];
        }
        $cart['count'] = $count;
        $cart['total'] = $total;
        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'cart' => [
                    'count' => $count,
                    'total' => $total,
                    'items' => $cart['items']
                ]
            ]);
        }

        return back()->with('success', 'Cantidad actualizada');
    }

    public function remove(Product $product)
    {
        $cart = session('cart', ['items' => [], 'count' => 0, 'total' => 0]);
        unset($cart['items'][$product->id]);

        $count = 0; $total = 0;
        foreach ($cart['items'] as $it) {
            $count += $it['qty'];
            $total += $it['price'] * $it['qty'];
        }
        $cart['count'] = $count;
        $cart['total'] = $total;

        session(['cart' => $cart]);

        if (request()->wantsJson()) {
            return response()->json([
                'ok' => true,
                'cart' => [
                    'count' => $count,
                    'total' => $total,
                    'items' => $cart['items']
                ]
            ]);
        }

        return back()->with('success', 'Producto eliminado del carrito');
    }
}