<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// auth endpointleri (giriş gerektirmez)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// giriş yapılmış kullanıcılar için
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // kategoriler - herkese açık okuma
    Route::get('/categories',      [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // ürünler - herkese açık okuma
    Route::get('/products',      [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // sepet işlemleri (giriş yapan herkes)
    Route::get('/cart',                    [CartController::class, 'index']);
    Route::post('/cart/items',             [CartController::class, 'addItem']);
    Route::put('/cart/items/{itemId}',     [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{itemId}',  [CartController::class, 'removeItem']);
    Route::delete('/cart',                 [CartController::class, 'clearCart']);

    // sadece adminlere açık işlemler
    Route::middleware('admin')->group(function () {

        // kategori yönetimi
        Route::post('/categories',        [CategoryController::class, 'store']);
        Route::put('/categories/{id}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // ürün yönetimi
        Route::post('/products',          [ProductController::class, 'store']);
        Route::put('/products/{id}',      [ProductController::class, 'update']);
        Route::delete('/products/{id}',   [ProductController::class, 'destroy']);
    });
});
