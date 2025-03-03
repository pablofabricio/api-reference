<?php

use App\Http\Controllers\MigrationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/duplicate-shop', [MigrationsController::class, 'duplicateShop']);

