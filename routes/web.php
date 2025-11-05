<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->get('/pedidos', function () {
    return view('pedidos.index');
})->name('orders.index');

// Catálogo (protegido)
Route::middleware('auth')->group(function () {
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/productos/data', [ProductController::class, 'data'])->name('products.data'); // devuelve JSON para AJAX
    Route::get('/productos/{product}/json', [ProductController::class, 'showJson'])->name('products.show.json');
});

// Carrito
Route::middleware('auth')->group(function () {
    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/carrito/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrito/{product}', [CartController::class, 'remove'])->name('cart.remove');
});

require __DIR__.'/auth.php';
