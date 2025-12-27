<?php

use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Protect API endpoints with JWT auth
Route::middleware('auth.jwt')->group(function () {
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::get('{id}', [NoteController::class, 'show']);
        Route::put('{id}', [NoteController::class, 'update']);
        Route::delete('{id}', [NoteController::class, 'destroy']);
    });

    // Resource routes for other models
    Route::apiResource('channels', App\Http\Controllers\ChannelController::class)->only(['index','store','show','update','destroy']);
    Route::get('channels/{id}/with-references', [App\Http\Controllers\ChannelController::class, 'withReferences']);
    Route::apiResource('channel-members', App\Http\Controllers\ChannelMemberController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('channel-references', App\Http\Controllers\ChannelReferenceController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('libraries', App\Http\Controllers\LibraryController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('library-items', App\Http\Controllers\LibraryItemController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('references', App\Http\Controllers\ReferenceController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('reference-nodes', App\Http\Controllers\ReferenceNodeController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('note-reference-added', App\Http\Controllers\NoteReferenceAddedController::class)->only(['index','store','show','update','destroy']);
    Route::apiResource('users', App\Http\Controllers\UserController::class)->only(['index','store','show','update','destroy']);
});

// Authentication routes
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth.jwt')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
});