<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'price',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    /**
     * Get validation rules for product
     */
    public static function getValidationRules(): array
    {
        return [
            'merchant_id' => 'required|exists:merchants,id',
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ];
    }

    /**
     * Get the merchant that owns the product
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the transactions for this product
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
