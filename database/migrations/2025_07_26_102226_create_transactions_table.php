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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('merchant_id')->constrained('merchants');
            $table->integer('start'); // jam mulai booking
            $table->integer('total_price'); // total harga semua jam
            $table->string('status'); // pending, confirmed, cancelled, completed
            $table->string('payment_method'); // cash, transfer, ewallet
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
