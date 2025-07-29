<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Models\Merchant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
                // Custom validation rule untuk business hours
        Validator::extend('business_hours', function ($attribute, $value, $parameters, $validator) {
            $open = request()->input('open');
            $close = request()->input('close');

            if ($open !== null && $close !== null && $close <= $open) {
                return false;
            }

            return true;
        }, 'Jam tutup harus lebih besar dari jam buka.');

        // Custom validation rule untuk booking conflict
        Validator::extend('no_booking_conflict', function ($attribute, $value, $parameters, $validator) {
            $productId = request()->input('product_id');
            $start = request()->input('start');
            $quantity = request()->input('quantity');
            $excludeId = request()->input('cart_id'); // untuk update

            if (!$productId || !$start || !$quantity) {
                return true; // skip validation jika data tidak lengkap
            }

            return !\App\Models\Cart::hasBookingConflict($productId, $start, $quantity, $excludeId);
        }, 'Lapangan sudah dibooking untuk waktu tersebut.');
    }
}
