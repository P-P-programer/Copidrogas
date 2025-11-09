<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Receta;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->with('items')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ship_name' => ['required','string','max:120'],
            'ship_phone' => ['nullable','string','max:30'],
            'ship_address' => ['required','string','max:255'],
            'ship_city' => ['required','string','max:120'],
            'ship_notes' => ['nullable','string','max:1000'],
        ]);

        $userId = auth()->id();

        $order = DB::transaction(function () use ($data, $userId) {
            $items = CartItem::with('product')->where('user_id', $userId)->get();
            if ($items->isEmpty()) {
                // Se lanza excepción para salir correctamente
                abort(422, 'Tu carrito está vacío.');
            }

            $subtotal = 0;
            foreach ($items as $ci) {
                $subtotal += $ci->qty * (float)$ci->product->price;
            }

            $shipping = 0;
            $total = $subtotal + $shipping;

            $order = Order::create([
                'user_id' => $userId,
                'status' => 'placed',
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $total,
                'ship_name' => $data['ship_name'],
                'ship_phone' => $data['ship_phone'] ?? null,
                'ship_address' => $data['ship_address'],
                'ship_city' => $data['ship_city'],
                'ship_notes' => $data['ship_notes'] ?? null,
            ]);

            foreach ($items as $ci) {
                $product = Product::where('id', $ci->product_id)->lockForUpdate()->first();
                if ($product->stock !== null && $product->stock < $ci->qty) {
                    abort(409, "Stock insuficiente para {$product->name}");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $ci->product_id,
                    'qty' => $ci->qty,
                    'unit_price' => (float)$product->price,
                    'total' => $ci->qty * (float)$product->price,
                ]);

                if ($product->stock !== null) {
                    $product->decrement('stock', $ci->qty);
                }
            }

            CartItem::where('user_id', $userId)->delete();

            return $order;
        });

        $redirectUrl = route('orders.show', $order);

        // Respuesta JSON para fetch
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'order_id' => $order->id,
                'redirect' => $redirectUrl,
                'message' => 'Pedido creado con éxito.'
            ]);
        }

        // Fallback normal
        return redirect($redirectUrl)->with('status', 'Pedido creado con éxito.');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items.product');
        return view('orders.show', compact('order'));
    }

    public function uploadReceta(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $request->validate([
            'receta_pdf' => 'required|file|mimes:pdf|max:5120', // 5MB
        ]);

        $file = $request->file('receta_pdf');
        $path = $file->store('recetas', 'public');

        $receta = Receta::create([
            'order_id' => $order->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('orders.show', $order)
            ->with('status', 'Receta subida correctamente.');
    }
}
