<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductReviewController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/refresh',  [AuthController::class, 'refresh']);

// Endpoints publicos
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/best', [ProductController::class, 'best']);
Route::get('/products/{product}', [ProductController::class, 'show']);


// Endpoints protegidos requieren usuario autenticado - Sanctum o similar
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']); //clase protegida de ejemplo, retorna info de el usuario
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ProductReviewController::class, 'update']);
    Route::patch('/reviews/{review}', [ProductReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy']);
});
