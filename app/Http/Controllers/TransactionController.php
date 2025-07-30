<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Payment;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Create transaction from cart
     */
    public function store(Request $request): JsonResponse
    {
        // Cek dulu apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Akses ditolak: Anda belum login atau token tidak valid.',
                'error' => 'UNAUTHENTICATED',
                'hint' => 'Pastikan sudah login dan mengirimkan Authorization: Bearer <token>'
            ], 401);
        }

        // Definisikan rules validasi
        $rules = [
            'payment_method' => 'required|string|in:cash,transfer,ewallet',
        ];

        // Custom pesan error per field
        $messages = [
            'payment_method.required' => 'Metode pembayaran wajib diisi.',
            'payment_method.in' => 'Metode pembayaran harus cash, transfer, atau ewallet.',
        ];

        // Jalankan validasi manual
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray(),
                'hint' => 'Periksa kembali data yang dikirim'
            ], 422);
        }

        // Ambil semua item di cart user
        $cartItems = Cart::where('user_id', Auth::id())->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart kosong'
            ], 400);
        }

        // Group cart items by merchant
        $groupedCart = $cartItems->groupBy('merchant_id');

                $transactions = [];

        foreach ($groupedCart as $merchantId => $items) {
            try {
                DB::beginTransaction();

                // Hitung total price untuk merchant ini
                $totalPrice = $items->sum('total_price');
                $startTime = $items->first()->start; // Ambil start time dari item pertama

                // Buat transaction
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'merchant_id' => $merchantId,
                    'start' => $startTime,
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'payment_method' => $request->payment_method,
                ]);

                // Buat transaction details untuk setiap jam
                foreach ($items as $cartItem) {
                    for ($hour = 0; $hour < $cartItem->quantity; $hour++) {
                        $currentHour = $cartItem->start + $hour;
                        if ($currentHour >= 24) {
                            $currentHour = $currentHour - 24;
                        }

                        TransactionDetail::create([
                            'transaction_id' => $transaction->id,
                            'product_id' => $cartItem->product_id,
                            'hour' => $currentHour,
                            'price_per_hour' => $cartItem->price_per_hour,
                        ]);
                    }
                }

                // Buat payment otomatis untuk transaction ini
                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'merchant_id' => $merchantId,
                    'user_id' => Auth::id(),
                    'total' => $totalPrice,
                    'status' => 'pending',
                ]);

                // Hit EduPay API untuk semua payment method
                // Load transaction dengan merchant dan user relationship
                $transactionWithRelations = $transaction->load(['merchant.user']);

                $edupayResponse = $this->edupayCreatePayment(
                    $payment->code,
                    $totalPrice,
                    $transactionWithRelations->merchant->user->email
                );

                if (!$edupayResponse) {
                    // Jika EduPay API gagal, rollback database transaction
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Gagal membuat payment di EduPay. Silakan coba lagi.',
                        'error' => 'EDUPAY_API_ERROR',
                        'hint' => 'Terjadi kesalahan saat menghubungi payment gateway'
                    ], 500);
                }

                // Update payment dengan response dari EduPay
                $payment->update([
                    'edupay_payment_id' => $edupayResponse['payment_id'] ?? null,
                    'edupay_payment_url' => $edupayResponse['payment_url'] ?? null,
                ]);

                // Commit database transaction
                DB::commit();

                $transactions[] = $transaction->load(['merchant', 'transactionDetails.product', 'payment']);

            } catch (\Exception $e) {
                // Rollback database transaction jika terjadi error
                DB::rollBack();

                Log::error('Checkout Error', [
                    'user_id' => Auth::id(),
                    'merchant_id' => $merchantId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'message' => 'Terjadi kesalahan saat membuat booking. Silakan coba lagi.',
                    'error' => 'DATABASE_ERROR',
                    'hint' => 'Terjadi kesalahan pada database'
                ], 500);
            }
        }

        // Clear cart setelah checkout
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'message' => 'Booking berhasil dibuat',
            'transactions' => $transactions,
            'total_transactions' => count($transactions),
            'payment_info' => [
                'total_payments' => count($transactions),
                'total_amount' => 'Rp ' . number_format(collect($transactions)->sum('total_price'), 0, ',', '.'),
            ]
        ], 201);
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        // Cek dulu apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Akses ditolak: Anda belum login atau token tidak valid.',
                'error' => 'UNAUTHENTICATED',
                'hint' => 'Pastikan sudah login dan mengirimkan Authorization: Bearer <token>'
            ], 401);
        }

        // Definisikan rules validasi
        $rules = [
            'status' => 'sometimes|required|string|in:pending,confirmed,cancelled,completed',
            'payment_method' => 'sometimes|string|in:cash,transfer,ewallet',
        ];

        // Custom pesan error per field
        $messages = [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus pending, confirmed, cancelled, atau completed.',
            'payment_method.required' => 'Metode pembayaran wajib diisi.',
            'payment_method.in' => 'Metode pembayaran harus cash, transfer, atau ewallet.',
        ];

        // Jalankan validasi manual
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray(),
                'hint' => 'Periksa kembali data yang dikirim'
            ], 422);
        }

        $transaction->update($validator->validated());

        // Update payment status sesuai dengan transaction status
        if ($transaction->payment) {
            $paymentStatus = match($transaction->status) {
                'cancelled' => 'failed',
                'confirmed' => 'pending', // Tetap pending sampai dibayar
                'completed' => 'success',
                default => 'pending'
            };

            $transaction->payment->update([
                'status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'success' ? now() : null
            ]);
        }

        return response()->json([
            'message' => 'Booking berhasil diperbarui',
            'data' => $transaction->load(['merchant', 'transactionDetails.product', 'payment']),
        ]);
    }

    /**
     * Get available time slots for a product
     */
    public function getAvailableTimeSlots(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $product = Product::find($request->product_id);
        $merchant = $product->merchant;

        // Ambil jam operasional merchant
        $openHour = $merchant->open;
        $closeHour = $merchant->close;

        // Ambil booking yang sudah ada untuk product tersebut
        $existingBookings = TransactionDetail::where('product_id', $request->product_id)
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', '!=', 'cancelled')  // ✅ Exclude cancelled
            ->pluck('transaction_details.hour')
            ->toArray();

        $availableSlots = [];

        // Generate time slots dari jam buka sampai jam tutup
        for ($hour = $openHour; $hour < $closeHour; $hour++) {
            if (!in_array($hour, $existingBookings)) {
                $availableSlots[] = [
                    'hour' => $hour,
                    'formatted_time' => sprintf('%02d:00', $hour),
                ];
            }
        }

        return response()->json([
            'product' => $product,
            'merchant_hours' => [
                'open' => sprintf('%02d:00', $openHour),
                'close' => sprintf('%02d:00', $closeHour),
            ],
            'available_slots' => $availableSlots,
        ]);
    }

    /**
     * Get user's booking history
     */
    public function getUserBookings(): JsonResponse
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['merchant', 'transactionDetails.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'bookings' => $transactions->map(function ($transaction) {
                $firstDetail = $transaction->transactionDetails->first();
                $lastDetail = $transaction->transactionDetails->last();

                return [
                    'id' => $transaction->id,
                    'merchant_name' => $transaction->merchant->name,
                    'start_time' => $firstDetail ? sprintf('%02d:00', $firstDetail->hour) : 'N/A',
                    'end_time' => $lastDetail ? sprintf('%02d:00', $lastDetail->hour + 1) : 'N/A',
                    'duration' => $transaction->total_quantity . ' jam',
                    'total_price' => 'Rp ' . number_format($transaction->total_price, 0, ',', '.'),
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'booking_date' => $transaction->created_at->format('d/m/Y H:i'),
                    'payment' => $transaction->payment ? [
                        'code' => $transaction->payment->code,
                        'status' => $transaction->payment->status,
                        'total' => $transaction->payment->formatted_total,
                        'paid_at' => $transaction->payment->paid_at?->format('d/m/Y H:i'),
                    ] : null,
                    'fields' => $transaction->transactionDetails->groupBy('product_id')->map(function ($details) {
                        $product = $details->first()->product;
                        return [
                            'field_name' => $product->name,
                            'hours' => $details->map(function ($detail) {
                                return sprintf('%02d:00', $detail->hour);
                            })->toArray(),
                        ];
                    })->values(),
                ];
            })
        ]);
    }

    private function edupayCreatePayment($code, $total, $email)
    {
        try {
            $response = Http::post('https://edupay.justputoff.com/api/service/storePayment', [
                'service_id' => 10, // Service ID untuk Sportlodek
                'total' => $total,
                'code' => $code,
                'email' => $email,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('EduPay API Error', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'service_id' => 10,
                        'total' => $total,
                        'code' => $code,
                        'email' => $email,
                    ]
                ]);

                return null;
            }
        } catch (\Exception $e) {
            Log::error('EduPay API Exception', [
                'message' => $e->getMessage(),
                'request' => [
                    'service_id' => 10,
                    'total' => $total,
                    'code' => $code,
                    'email' => $email,
                ]
            ]);

            return null;
        }
    }
}
