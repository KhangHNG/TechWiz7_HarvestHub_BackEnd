<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Login
Route::post('/login', [AuthController::class, 'login']);

// Product
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// Category
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

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

