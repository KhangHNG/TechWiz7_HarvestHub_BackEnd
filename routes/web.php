<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyFromLink'])
    ->middleware('signed')
    ->name('verification.verify');
