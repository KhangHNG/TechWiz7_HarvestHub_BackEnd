<?php

use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');

Route::middleware(['role:CUSTOMER,FARMER,ADMIN'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/ai/chat', [AiChatController::class, 'chat']);
});

Route::middleware(['role:CUSTOMER'])->group(function () {
    Route::get('/customer/ping', [AuthController::class, 'me']);
});

Route::middleware(['role:FARMER'])->group(function () {
    Route::get('/farmer/ping', [AuthController::class, 'me']);
});
