<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Landing\WelcomeController;
use App\Http\Controllers\Landing\CartController as LandingCartController;
use App\Http\Controllers\Landing\ProfileController as LandingProfileController;
use App\Http\Controllers\Landing\TransactionController as LandingTransactionController;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\ProductController;
use App\Http\Controllers\Merchant\TransactionController;
use App\Http\Controllers\Merchant\PaymentController;
use App\Http\Controllers\Merchant\ProfileController as MerchantProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

// Landing page route (hapus yang lama)
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/merchant/{merchantId}/details', [WelcomeController::class, 'getMerchantDetails'])->name('merchant.details');
Route::get('/merchant/all', [WelcomeController::class, 'getAllMerchants'])->name('merchant.all');

// Test route untuk debug role
Route::get('/test-role', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_merchant' => $user->isMerchant(),
            'is_regular_user' => $user->isRegularUser(),
            'role' => $user->getRole(),
            'has_merchant' => $user->merchant ? 'Yes' : 'No',
            'merchant_name' => $user->merchant?->name ?? 'N/A'
        ]);
    }
    return response()->json(['message' => 'Not authenticated']);
})->middleware('auth');

// Auth required routes with role validation
Route::middleware(['auth', 'check.role'])->group(function () {
    // User routes (regular users)
    Route::get('/cart', [LandingCartController::class, 'index'])->name('cart');
    Route::get('/transaction', [LandingTransactionController::class, 'index'])->name('transaction');
    Route::get('/profile-mobile', [LandingProfileController::class, 'index'])->name('profile.mobile');
    Route::put('/api/profile', [LandingProfileController::class, 'update']);
    Route::put('/api/change-password', [LandingProfileController::class, 'changePassword']);

    // Merchant routes
    Route::prefix('merchant')->name('merchant.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/products', [ProductController::class, 'index'])->name('products');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('/profile', [MerchantProfileController::class, 'index'])->name('profile');
    });
});

Route::get('/dashboard', function () {
    redirect()->route('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');
    return response()->json([
        'message' => 'Database migrated and seeded successfully',
        'timestamp' => now()->format('Y-m-d H:i:s'),
    ]);
});

require __DIR__.'/auth.php';
