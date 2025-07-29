<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class Merchant extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'status',
        'open',
        'close',
        'banner',
    ];

    /**
     * Get validation rules for merchant
     */
    public static function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'status' => 'required|string|in:active,inactive,pending',
            'open' => 'required|integer|min:0|max:24',
            'close' => 'required|integer|min:0|max:24|business_hours',
            'banner' => 'nullable|string|max:255',
        ];
    }



    /**
     * Get the user that owns the merchant
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products (fields) for this merchant
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the transactions for this merchant
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
