<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('merchant_id')->constrained('merchants');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('start'); // jam mulai booking
            $table->integer('quantity'); // jumlah jam booking
            $table->integer('price_per_hour'); // harga per jam
            $table->integer('total_price'); // total harga (price_per_hour * quantity)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
