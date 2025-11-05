<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.index', compact('categories'));
    }

    // Devuelve JSON filtrado (para AJAX)
    public function data(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('category') && $request->category !== '') {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }

        $products = $query->orderBy('name')->paginate(24);

        // transformar productos mínimos para el cliente
        $data = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'image' => $p->image,
            'category' => $p->category,
            'stock' => $p->stock,
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    // Detalle JSON de un producto
    public function showJson(Product $product)
    {
        $product->load('category','provider');
        return response()->json($product);
    }
}
