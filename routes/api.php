<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::post('/', [BookController::class, 'store']);
    Route::get('{id}', [BookController::class, 'show']);
    Route::put('{id}', [BookController::class, 'update']);
    Route::delete('{id}', [BookController::class, 'destroy']);
});

// 📝 Notes - CRUD
Route::prefix('notes')->group(function () {
    Route::get('/', [NoteController::class, 'index']);
    Route::post('/', [NoteController::class, 'store']);
    Route::get('{id}', [NoteController::class, 'show']);
    Route::put('{id}', [NoteController::class, 'update']);
    Route::delete('{id}', [NoteController::class, 'destroy']);
});

// 📖 Chapters - Somente GET
Route::prefix('chapters')->group(function () {
    Route::get('/', [ChapterController::class, 'index']);
    Route::get('{id}', [ChapterController::class, 'show']);
});