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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('merchant_id')->constrained('merchants');
            $table->foreignId('user_id')->constrained('users');
            $table->string('code')->unique(); // kode pembayaran dari EduPay
            $table->integer('total'); // total pembayaran
            $table->string('status')->default('pending'); // pending, success, failed, expired
            $table->timestamp('paid_at')->nullable(); // waktu pembayaran berhasil
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
