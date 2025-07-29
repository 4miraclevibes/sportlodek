<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Create payment for transaction
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'merchant_id' => 'required|exists:merchants,id',
        ]);

        $transaction = Transaction::find($request->transaction_id);

        // Cek apakah transaction sudah ada payment
        if ($transaction->payment) {
            return response()->json([
                'message' => 'Transaction sudah memiliki payment'
            ], 400);
        }

        // Buat payment
        $payment = Payment::create([
            'transaction_id' => $request->transaction_id,
            'merchant_id' => $request->merchant_id,
            'user_id' => Auth::id(),
            'total' => $transaction->total_price,
            'status' => 'pending',
        ]);

        // TODO: Integrasi dengan EduPay Gateway
        // $edupayResponse = $this->createEduPayPayment($payment);

        return response()->json([
            'message' => 'Payment berhasil dibuat',
            'data' => $payment->load(['transaction', 'merchant']),
            'payment_info' => [
                'code' => $payment->code,
                'total' => $payment->formatted_total,
                'status' => $payment->status,
                'edupay_url' => $this->generateEduPayUrl($payment), // URL untuk redirect ke EduPay
            ]
        ], 201);
    }

    /**
     * Get payment details
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'code' => $payment->code,
                'total' => $payment->formatted_total,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->format('d/m/Y H:i'),
                'created_at' => $payment->created_at->format('d/m/Y H:i'),
            ],
            'transaction' => [
                'id' => $payment->transaction->id,
                'start_time' => $payment->transaction->formatted_start_time,
                'duration' => $payment->transaction->total_quantity . ' jam',
                'total_price' => 'Rp ' . number_format($payment->transaction->total_price, 0, ',', '.'),
            ],
            'merchant' => [
                'id' => $payment->merchant->id,
                'name' => $payment->merchant->name,
            ],
            'user' => [
                'id' => $payment->user->id,
                'name' => $payment->user->name,
            ]
        ]);
    }

    /**
     * Update payment status (callback dari EduPay)
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'status' => 'required|string|in:success,failed,expired',
            'signature' => 'required|string', // untuk validasi callback
        ]);

        $payment = Payment::where('code', $request->code)->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment tidak ditemukan'
            ], 404);
        }

        // TODO: Validasi signature dari EduPay
        // if (!$this->validateEduPaySignature($request)) {
        //     return response()->json(['message' => 'Invalid signature'], 400);
        // }

        // Update status payment
        switch ($request->status) {
            case 'success':
                $payment->markAsSuccessful();
                // Update transaction status juga
                $payment->transaction->update(['status' => 'confirmed']);
                break;
            case 'failed':
                $payment->markAsFailed();
                break;
            case 'expired':
                $payment->markAsExpired();
                break;
        }

        return response()->json([
            'message' => 'Payment status berhasil diperbarui',
            'payment' => [
                'code' => $payment->code,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments(): JsonResponse
    {
        $payments = Payment::where('user_id', Auth::id())
            ->with(['transaction', 'merchant'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'payments' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'code' => $payment->code,
                    'total' => $payment->formatted_total,
                    'status' => $payment->status,
                    'merchant_name' => $payment->merchant->name,
                    'transaction_info' => [
                        'start_time' => $payment->transaction->formatted_start_time,
                        'duration' => $payment->transaction->total_quantity . ' jam',
                    ],
                    'created_at' => $payment->created_at->format('d/m/Y H:i'),
                    'paid_at' => $payment->paid_at?->format('d/m/Y H:i'),
                ];
            })
        ]);
    }

    /**
     * Check payment status
     */
    public function checkStatus(Payment $payment): JsonResponse
    {
        // TODO: Cek status di EduPay Gateway
        // $edupayStatus = $this->checkEduPayStatus($payment->code);

        return response()->json([
            'payment' => [
                'code' => $payment->code,
                'status' => $payment->status,
                'total' => $payment->formatted_total,
                'paid_at' => $payment->paid_at?->format('d/m/Y H:i'),
            ],
            'transaction' => [
                'status' => $payment->transaction->status,
            ]
        ]);
    }

    /**
     * Generate EduPay URL (placeholder)
     */
    private function generateEduPayUrl(Payment $payment): string
    {
        // TODO: Implementasi sesuai EduPay Gateway
        return "https://edupay.example.com/pay?code={$payment->code}&amount={$payment->total}";
    }

    /**
     * Create payment di EduPay (placeholder)
     */
    private function createEduPayPayment(Payment $payment): array
    {
        // TODO: Implementasi sesuai EduPay Gateway
        return [
            'success' => true,
            'payment_url' => $this->generateEduPayUrl($payment),
            'expires_at' => now()->addHours(24)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Validate EduPay signature (placeholder)
     */
    private function validateEduPaySignature(Request $request): bool
    {
        // TODO: Implementasi validasi signature sesuai EduPay
        return true;
    }

    /**
     * Check status di EduPay (placeholder)
     */
    private function checkEduPayStatus(string $code): array
    {
        // TODO: Implementasi sesuai EduPay Gateway
        return [
            'status' => 'pending',
            'message' => 'Payment masih dalam proses',
        ];
    }
}
