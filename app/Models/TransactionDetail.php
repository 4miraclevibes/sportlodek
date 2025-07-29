<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'hour',
        'price_per_hour',
    ];

    protected $casts = [
        'hour' => 'integer',
        'price_per_hour' => 'integer',
    ];

    /**
     * Get validation rules for transaction detail
     */
    public static function getValidationRules(): array
    {
        return [
            'transaction_id' => 'required|exists:transactions,id',
            'product_id' => 'required|exists:products,id',
            'hour' => 'required|integer|min:0|max:23',
            'price_per_hour' => 'required|integer|min:0',
        ];
    }

    /**
     * Get the transaction that owns this detail
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the product (field/lapangan)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted hour
     */
    public function getFormattedHourAttribute(): string
    {
        return sprintf('%02d:00', $this->hour);
    }
}
