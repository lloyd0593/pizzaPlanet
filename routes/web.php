<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AuthController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PizzaManager;
use App\Livewire\Admin\ToppingManager;
use App\Livewire\Admin\OrderManager;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Routes (Blade + Controllers, NO Livewire)
|--------------------------------------------------------------------------
*/

// Home / Menu
Route::get('/', [MenuController::class, 'index'])->name('menu');
Route::get('/pizza/{pizza}', [MenuController::class, 'show'])->name('pizza.show');
Route::get('/customize', [MenuController::class, 'customize'])->name('pizza.customize');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/{cartItemId}', [CartController::class, 'update'])->name('update');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::delete('/{cartItemId}', [CartController::class, 'remove'])->name('remove');
});

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/{orderId}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/checkout/{orderId}/payment', [CheckoutController::class, 'processPayment'])->name('checkout.processPayment');
Route::get('/order/{orderId}/confirmation', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

/*
|--------------------------------------------------------------------------
| Admin Routes (Livewire)
|--------------------------------------------------------------------------
*/

// Admin Auth
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Panel (protected by admin middleware)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/pizzas', PizzaManager::class)->name('pizzas');
    Route::get('/toppings', ToppingManager::class)->name('toppings');
    Route::get('/orders', OrderManager::class)->name('orders');
});
