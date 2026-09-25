<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\FarmerApiController;
use App\Http\Controllers\Api\ProductsApiController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductsApiController::class, 'index']);
Route::get('/products/{id}', [ProductsApiController::class, 'show'])->whereNumber('id');
Route::get('/categories', [CatalogApiController::class, 'categories']);
Route::get('/markets', [CatalogApiController::class, 'markets']);
Route::get('/farmers', [CatalogApiController::class, 'farmers']);
Route::get('/farmers/{id}', [CatalogApiController::class, 'farmer'])->whereNumber('id');


Route::middleware(['role:CUSTOMER,FARMER,ADMIN'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['role:CUSTOMER'])->group(function () {
    Route::get('/customer/ping', [AuthController::class, 'me']);
    Route::get('/customer/profile', [CustomerApiController::class, 'profile']);
    Route::get('/customer/orders', [CustomerApiController::class, 'orders']);
    Route::get('/customer/cart', [CustomerApiController::class, 'cart']);
    Route::get('/customer/wishlist', [CustomerApiController::class, 'wishlist']);
    Route::get('/customer/following', [CustomerApiController::class, 'following']);
});

Route::middleware(['role:FARMER'])->group(function () {
    Route::get('/farmer/ping', [AuthController::class, 'me']);
    Route::get('/farmer/profile', [FarmerApiController::class, 'profile']);
    Route::get('/farmer/products', [FarmerApiController::class, 'products']);
    Route::get('/farmer/orders', [FarmerApiController::class, 'orders']);
});

