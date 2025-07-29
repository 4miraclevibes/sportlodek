<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (tidak perlu authentication)
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Test routes - KELUARKAN DARI AUTH
//Route::put('/test-profile', [UserController::class, 'updateProfile']);
//Route::put('/test-change-password', [UserController::class, 'changePassword']);

// Protected routes (perlu authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile-update', [UserController::class, 'updateProfile']);
    Route::put('/password-change', [UserController::class, 'changePassword']);
    Route::post('/refresh-token', [UserController::class, 'refreshToken']);

    // Merchant routes
    Route::post('/merchants', [MerchantController::class, 'store']);
    Route::put('/merchants/{merchant}', [MerchantController::class, 'update']);
    Route::get('/merchants/validation-rules', [MerchantController::class, 'getValidationRules']);

    // Product routes
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::get('/products/merchant/{merchant}', [ProductController::class, 'getByMerchant']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Transaction routes
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::get('/transactions/available-slots', [TransactionController::class, 'getAvailableTimeSlots']);
    Route::get('/transactions/my-bookings', [TransactionController::class, 'getUserBookings']);

    // Payment routes
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::post('/payments/status', [PaymentController::class, 'updateStatus']);
    Route::get('/payments/my-payments', [PaymentController::class, 'getUserPayments']);
    Route::get('/payments/{payment}/status', [PaymentController::class, 'checkStatus']);
});

// Test route untuk cek API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Sportlodek berjalan dengan baik!',
        'timestamp' => now()->format('Y-m-d H:i:s'),
    ]);
});
