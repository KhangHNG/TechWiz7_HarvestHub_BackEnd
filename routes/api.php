<?php

use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\FarmerFollowController;
use App\Http\Controllers\Api\FarmerRevenueController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'findById']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'findById']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'findById']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'findById']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

Route::get('/farmers', [FarmerController::class, 'index']);
Route::post('/farmers', [FarmerController::class, 'store']);
Route::get('/farmers/{id}', [FarmerController::class, 'findById']);
Route::put('/farmers/{id}', [FarmerController::class, 'update']);
Route::delete('/farmers/{id}', [FarmerController::class, 'destroy']);

Route::get('/markets', [MarketController::class, 'index']);
Route::post('/markets', [MarketController::class, 'store']);
Route::get('/markets/{id}', [MarketController::class, 'findById']);
Route::put('/markets/{id}', [MarketController::class, 'update']);
Route::delete('/markets/{id}', [MarketController::class, 'destroy']);

Route::get('/wishlists', [WishlistController::class, 'index']);
Route::post('/wishlists', [WishlistController::class, 'store']);
Route::get('/wishlists/{id}', [WishlistController::class, 'findById']);
Route::put('/wishlists/{id}', [WishlistController::class, 'update']);
Route::delete('/wishlists/{id}', [WishlistController::class, 'destroy']);

Route::get('/follows', [FarmerFollowController::class, 'index']);
Route::post('/follows', [FarmerFollowController::class, 'store']);
Route::get('/follows/{id}', [FarmerFollowController::class, 'findById']);
Route::put('/follows/{id}', [FarmerFollowController::class, 'update']);
Route::delete('/follows/{id}', [FarmerFollowController::class, 'destroy']);

Route::middleware(['role:CUSTOMER,FARMER,ADMIN'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/ai/chat', [AiChatController::class, 'chat']);
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
});

Route::middleware(['role:CUSTOMER'])->group(function () {
    Route::get('/customer/ping', [AuthController::class, 'me']);
});

Route::middleware(['role:FARMER'])->group(function () {
    Route::get('/farmer/ping', [AuthController::class, 'me']);
    Route::get('/farmer/revenue', [FarmerRevenueController::class, 'index']);
});
