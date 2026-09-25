<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\FarmerFollowController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

// Login
Route::post('/login', [AuthController::class, 'login']);

// Product
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'findById']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// Category
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'findById']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// User
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'findById']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Order
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'findById']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

// Farmer
Route::get('/farmers', [FarmerController::class, 'index']);
Route::post('/farmers', [FarmerController::class, 'store']);
Route::get('/farmers/{id}', [FarmerController::class, 'findById']);
Route::put('/farmers/{id}', [FarmerController::class, 'update']);
Route::delete('/farmers/{id}', [FarmerController::class, 'destroy']);

// Market
Route::get('/markets', [MarketController::class, 'index']);
Route::post('/markets', [MarketController::class, 'store']);
Route::get('/markets/{id}', [MarketController::class, 'findById']);
Route::put('/markets/{id}', [MarketController::class, 'update']);
Route::delete('/markets/{id}', [MarketController::class, 'destroy']);

// Wishlist
Route::get('/wishlists', [WishlistController::class, 'index']);
Route::post('/wishlists', [WishlistController::class, 'store']);
Route::get('/wishlists/{id}', [WishlistController::class, 'findById']);
Route::put('/wishlists/{id}', [WishlistController::class, 'update']);
Route::delete('/wishlists/{id}', [WishlistController::class, 'destroy']);

// Farmer follow
Route::get('/follows', [FarmerFollowController::class, 'index']);
Route::post('/follows', [FarmerFollowController::class, 'store']);
Route::get('/follows/{id}', [FarmerFollowController::class, 'findById']);
Route::put('/follows/{id}', [FarmerFollowController::class, 'update']);
Route::delete('/follows/{id}', [FarmerFollowController::class, 'destroy']);

//Detail
Route::middleware(['role:CUSTOMER,FARMER,ADMIN'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['role:CUSTOMER'])->group(function () {
    Route::get('/customer/ping', [AuthController::class, 'me']);
});

Route::middleware(['role:FARMER'])->group(function () {
    Route::get('/farmer/ping', [AuthController::class, 'me']);
});
