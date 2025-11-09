<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockLog;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Vista principal de gestión de stock
     */
    public function index()
    {
        $this->authorize('manage-products');
        $categories = Category::orderBy('name')->get();
        return view('stock.index', compact('categories'));
    }

    /**
     * JSON de productos para búsqueda/filtrado
     */
    public function data(Request $request)
    {
        $this->authorize('manage-products');

        $query = Product::with('category:id,name');
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');

        $products = $query->orderBy('name')->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'stock' => $p->stock,
            'image' => $p->image,          // solo nombre
            'category' => $p->category? ['id'=>$p->category->id,'name'=>$p->category->name]:null,
        ]);

        return response()->json($products);
    }

    /**
     * Actualizar stock de un producto (con log)
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('manage-products');

        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        $before = $product->stock;
        $after = (int) $request->input('stock');

        // Actualizar producto
        $product->stock = $after;
        $product->save();

        // Registrar log
        StockLog::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'before_stock' => $before,
            'after_stock' => $after,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Stock actualizado correctamente.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
            ]
        ]);
    }
}
