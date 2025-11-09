<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class AnalyticsController extends Controller
{
    /**
     * Muestra dashboard de analítica.
     * Super Admin ve panel adicional de actividad.
     */
    public function index()
    {
        // Gate ya aplicado en rutas
        $activity = [
            'orders' => Order::with(['user.role'])->orderByDesc('created_at')->limit(10)->get(),
            'stock_updates' => Product::orderByDesc('updated_at')->limit(10)->get(['id','name','stock','updated_at']),
            'stock_logs' => \App\Models\StockLog::with(['user:id,name,email','product:id,name'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ];
        // Solo si la vista usa categorías:
        $categories = Category::orderBy('name')->get();

        return view('analytics.index', compact('activity', 'categories')); // quita $categories si no lo usas
    }

    /**
     * Obtiene últimas 10 órdenes con información de usuario y rol
     */
    private function getRecentOrders()
    {
        return Order::with(['user.role'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * Obtiene últimos 10 productos con cambios de stock
     */
    private function getRecentStockUpdates()
    {
        return Product::whereNotNull('updated_at')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get(['id', 'name', 'stock', 'updated_at']);
    }

    /**
     * Top 5 productos más vendidos (JSON para Chart.js)
     */
    public function topProducts()
    {
        $this->authorize('view-analytics');

        $data = OrderItem::select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'label' => $row->product?->name ?? "ID {$row->product_id}",
                'value' => (int)$row->total_qty
            ]);

        return response()->json($data);
    }

    /**
     * Top 5 productos con menor stock (JSON para Chart.js)
     */
    public function lowStock()
    {
        $this->authorize('view-analytics');

        $data = Product::select('name','stock')
            ->whereNotNull('stock')
            ->orderBy('stock','asc')
            ->limit(5)
            ->get()
            ->map(fn($p) => ['label' => $p->name, 'value' => (int)$p->stock]);

        return response()->json($data);
    }
}
