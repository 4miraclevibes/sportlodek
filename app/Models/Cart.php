<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'product_id',
        'start',
        'quantity',
        'price_per_hour',
        'total_price',
    ];

    protected $casts = [
        'start' => 'integer',
        'quantity' => 'integer',
        'price_per_hour' => 'integer',
        'total_price' => 'integer',
    ];

    /**
     * Get validation rules for cart
     */
    public static function getValidationRules(): array
    {
        return [
            // user_id tidak perlu di-validate karena di-set otomatis
            'merchant_id' => 'required|exists:merchants,id',
            'product_id' => 'required|exists:products,id',
            'start' => 'required|integer|min:0|max:23|no_booking_conflict',
            'quantity' => 'required|integer|min:1|max:12',
            // price_per_hour dan total_price tidak perlu karena di-set otomatis
        ];
    }

    /**
     * Boot method untuk menghitung total price otomatis
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cart) {
            // Hitung total price otomatis
            $cart->total_price = $cart->price_per_hour * $cart->quantity;
        });

        static::updating(function ($cart) {
            // Hitung ulang total price saat update
            $cart->total_price = $cart->price_per_hour * $cart->quantity;
        });
    }

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the merchant
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the product (field/lapangan)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted start time
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return sprintf('%02d:00', $this->start);
    }

    /**
     * Get end time
     */
    public function getEndTimeAttribute(): int
    {
        $end = $this->start + $this->quantity;
        if ($end > 24) {
            $end = $end - 24;
        }
        return $end;
    }

    /**
     * Get formatted end time
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return sprintf('%02d:00', $this->end_time);
    }

    /**
     * Get booking duration in hours
     */
    public function getDurationAttribute(): int
    {
        return $this->quantity;
    }

    /**
     * Cek booking conflict di transaction_details dan cart
     */
    public static function hasBookingConflict($productId, $start, $quantity, $excludeCartId = null): bool
    {
        $hours = [];
        for ($i = 0; $i < $quantity; $i++) {
            $hour = $start + $i;
            if ($hour >= 24) $hour -= 24;
            $hours[] = $hour;
        }

        // Cek di transaction_details (booking yang sudah confirmed)
        $conflict = \App\Models\TransactionDetail::where('product_id', $productId)
            ->whereHas('transaction', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->whereIn('hour', $hours)
            ->exists();

        // Cek di cart user lain (cart yang belum di-checkout)
        $cartConflict = self::where('product_id', $productId)
            ->whereIn('start', $hours)
            ->when($excludeCartId, fn($q) => $q->where('id', '!=', $excludeCartId))
            ->exists();

        return $conflict || $cartConflict;
    }
}
