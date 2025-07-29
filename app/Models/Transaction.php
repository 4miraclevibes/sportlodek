<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'start',
        'total_price',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'start' => 'integer',
        'total_price' => 'integer',
    ];

    /**
     * Get validation rules for transaction
     */
    public static function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'merchant_id' => 'required|exists:merchants,id',
            'start' => 'required|integer|min:0|max:23',
            'total_price' => 'required|integer|min:0',
            'status' => 'required|string|in:pending,confirmed,cancelled,completed',
            'payment_method' => 'required|string|in:cash,transfer,ewallet',
        ];
    }

    /**
     * Get the user that made the booking
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
     * Get the transaction details (per jam booking)
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Get the payment for this transaction
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get formatted start time
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return sprintf('%02d:00', $this->start);
    }

    /**
     * Get total quantity (jumlah jam) dari transaction details
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->transactionDetails()->count();
    }

    /**
     * Get end time berdasarkan quantity
     */
    public function getEndTimeAttribute(): int
    {
        $end = $this->start + $this->total_quantity;
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
        return $this->total_quantity;
    }
}
