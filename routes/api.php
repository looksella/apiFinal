<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductReviewController;

// Endpoints publicos
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/best', [ProductController::class, 'best']);

// Endpoints protegidos requieren usuario autenticado - Sanctum o similar
Route::middleware('auth:sanctum')->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::patch('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    Route::post('products/{product}/reviews', [ProductReviewController::class, 'store']);
    Route::put('reviews/{review}', [ProductReviewController::class, 'update']);
    Route::patch('reviews/{review}', [ProductReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ProductReviewController::class, 'destroy']);
});
