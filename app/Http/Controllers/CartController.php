<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'qty' => ['required','integer','min:1','max:1000'],
        ]);

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json(['ok' => false, 'message' => 'Producto no está en el carrito'], 404);
        }

        $cartItem->update(['qty' => $validated['qty']]);

        $cart = $this->getCartData();

        return response()->json(['ok' => true, 'cart' => $cart]);
    }

    public function destroy($id)
    {
        CartItem::where('user_id', auth()->id())
            ->where('product_id', $id)
            ->delete();

        $cart = $this->getCartData();

        return response()->json(['ok' => true, 'cart' => $cart]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'ship_name' => 'required|string|max:255',
            'ship_phone' => 'nullable|string|max:50',
            'ship_address' => 'required|string|max:500',
            'ship_city' => 'required|string|max:100',
            'ship_notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tu carrito está vacío.'
            ], 422);
        }

        // Validar stock
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->qty) {
                return response()->json([
                    'ok' => false,
                    'message' => "Stock insuficiente para {$item->product->name}."
                ], 409);
            }
        }

        DB::beginTransaction();
        try {
            $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->qty);
            $shippingCost = 0;
            $total = $subtotal + $shippingCost;

            // Crear orden
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'placed',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'ship_name' => $validated['ship_name'],
                'ship_phone' => $validated['ship_phone'] ?? null,
                'ship_address' => $validated['ship_address'],
                'ship_city' => $validated['ship_city'],
                'ship_notes' => $validated['ship_notes'] ?? null,
            ]);

            // Crear items y descontar stock
            foreach ($cartItems as $item) {
                $unitPrice = $item->product->price;
                $itemTotal = $unitPrice * $item->qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'unit_price' => $unitPrice,
                    'total' => $itemTotal,
                ]);

                $item->product->decrement('stock', $item->qty);
            }

            // Vaciar carrito
            $user->cartItems()->delete();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Pedido creado exitosamente.',
                'redirect' => route('orders.show', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Error al procesar el pedido: ' . $e->getMessage()
            ], 500);
        }
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