<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserManagementController;

Route::get('/', function () {
    return view('welcome');
});

// Productos públicos
// Coloca primero la ruta JSON para evitar que /productos/{id} capture /productos/9/json
Route::get('/productos/{product}/json', [ProductController::class, 'showJson'])
    ->whereNumber('product')
    ->name('products.showJson');

Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
Route::get('/productos/data', [ProductController::class, 'data'])->name('products.data');
Route::get('/productos/{id}', [ProductController::class, 'show'])
    ->whereNumber('id')
    ->name('products.show');

// Contacto
Route::view('/contacto', 'contacto')->name('contacto');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Carrito (auth)
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->whereNumber('id')->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->whereNumber('id')->name('cart.destroy');

    // Checkout (ambos nombres)
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.store');
});

// Ping de sesión (se usaba en vistas.js)
Route::post('/session/ping', function () {
    return response()->json(['ok' => true]);
})->middleware('auth');

// Pedidos
Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/receta', [OrderController::class, 'uploadReceta'])->name('orders.uploadReceta');
});

// Analítica
Route::middleware(['auth', 'can:view-analytics'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/top-products', [AnalyticsController::class, 'topProducts']);
    Route::get('/analytics/low-stock', [AnalyticsController::class, 'lowStock']);
});

// Stock
Route::middleware(['auth', 'can:manage-products'])->group(function () {
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/data', [StockController::class, 'data'])->name('stock.data');
    Route::patch('/stock/{product}', [StockController::class, 'update'])->whereNumber('product')->name('stock.update');
});

// Usuarios
Route::middleware(['auth', 'can:manage-users'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserManagementController::class, 'data'])->name('users.data');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->whereNumber('user')->name('users.update');
});

require __DIR__.'/auth.php';
