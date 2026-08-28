<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);
    Route::get('posts/{slug}/comments', [CommentController::class, 'index']);
    Route::post('posts/{slug}/comments', [CommentController::class, 'store'])
        ->middleware(['auth:sanctum', 'throttle:comments']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{slug}', [TagController::class, 'show']);

    Route::get('search', [SearchController::class, 'index']);

    Route::post('auth/tokens', [AuthTokenController::class, 'store'])
        ->middleware('throttle:api-tokens');
    Route::delete('auth/tokens', [AuthTokenController::class, 'destroy'])
        ->middleware('auth:sanctum');
});
