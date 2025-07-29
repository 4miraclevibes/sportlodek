<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id',
        'merchant_id',
        'user_id',
        'code',
        'total',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'paid_at' => 'datetime',
    ];

    /**
     * Get validation rules for payment
     */
    public static function getValidationRules(): array
    {
        return [
            'transaction_id' => 'required|exists:transactions,id',
            'merchant_id' => 'required|exists:merchants,id',
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|unique:payments,code',
            'total' => 'required|integer|min:0',
            'status' => 'required|string|in:pending,success,failed,expired',
            'paid_at' => 'nullable|date',
        ];
    }

    /**
     * Boot method untuk generate code otomatis
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // Generate kode pembayaran otomatis jika tidak ada
            if (empty($payment->code)) {
                $payment->code = 'EDUPAY-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the transaction that owns the payment
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the merchant
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the user that made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Mark payment as successful
     */
    public function markAsSuccessful(): void
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Mark payment as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }
}
