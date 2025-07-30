<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Transaction;

class EdupayController extends Controller
{
    public function paymentNotification(Request $request, $code)
    {
        // Cari payment berdasarkan code dari URL parameter
        $payment = Payment::where('code', $code)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Update payment status
        $payment->update([
            'status' => $request->status,
            'paid_at' => $request->status === 'success' ? now() : null
        ]);

        // Sync transaction status berdasarkan payment status
        $transaction = $payment->transaction;
        if ($transaction) {
            $transactionStatus = match($request->status) {
                'success' => 'confirmed',
                'failed' => 'cancelled',
                'expired' => 'cancelled',
                default => 'pending'
            };

            $transaction->update([
                'status' => $transactionStatus
            ]);
        }

        return response()->json([
            'message' => 'Payment notification received',
            'payment_code' => $code,
            'payment_status' => $request->status,
            'transaction_status' => $transactionStatus ?? 'pending'
        ]);
    }
}
