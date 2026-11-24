<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class, 'index'])->name('customer.index');
Route::post('/checkout', [OrderController::class, 'store'])->name('customer.checkout');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('can:access-dashboard')->group(function () {
        Route::get('/dashboard', [OrderController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/admin/dashboard', [OrderController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('admin.orders.updateStatus');

        Route::get('/admin/reports', [OrderController::class, 'report'])->name('admin.reports');
    });

    Route::middleware('can:access-admin')->group(function () {
        Route::delete('/admin/orders/{order}', [OrderController::class, 'destroy'])
            ->name('admin.orders.destroy');
        Route::resource('/admin/laundry_items', LaundryItemController::class)
            ->names('laundry_items')
            ->except(['show'])
            ->parameters(['laundry_items' => 'laundryItem']);
    });
});
