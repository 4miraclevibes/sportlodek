<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;

class CartController extends Controller
{
    /**
     * Add item to cart
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
            'merchant_id' => 'required|exists:merchants,id',
            'product_id' => 'required|exists:products,id',
            'start' => 'required|integer|min:0|max:23|no_booking_conflict',
            'quantity' => 'required|integer|min:1|max:12',
        ];

        // Custom pesan error per field
        $messages = [
            'merchant_id.required' => 'Merchant wajib diisi.',
            'merchant_id.exists' => 'Merchant tidak ditemukan.',
            'product_id.required' => 'Lapangan wajib diisi.',
            'product_id.exists' => 'Lapangan tidak ditemukan.',
            'start.required' => 'Jam mulai wajib diisi.',
            'start.integer' => 'Jam mulai harus berupa angka.',
            'start.min' => 'Jam mulai minimal 0.',
            'start.max' => 'Jam mulai maksimal 23.',
            'start.no_booking_conflict' => 'Jam yang dipilih sudah dibooking.',
            'quantity.required' => 'Durasi wajib diisi.',
            'quantity.integer' => 'Durasi harus berupa angka.',
            'quantity.min' => 'Durasi minimal 1 jam.',
            'quantity.max' => 'Durasi maksimal 12 jam.',
        ];

        // Jalankan validasi manual
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            // Ambil semua error
            $errors = $validator->errors()->toArray();

            // Custom format response error
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $errors,
                'hint' => 'Periksa kembali data yang dikirim'
            ], 422);
        }

        // Set user_id dari user yang sedang login
        $validated = $validator->validated();
            $validated['user_id'] = Auth::id();

        // Ambil harga product
        $product = Product::find($validated['product_id']);
        $validated['price_per_hour'] = $product->price;

        $cart = Cart::create($validated);

        return response()->json([
            'message' => 'Item berhasil ditambahkan ke cart',
            'data' => $cart->load(['product', 'merchant']),
            'cart_info' => [
                'field_name' => $cart->product->name,
                'start_time' => $cart->formatted_start_time,
                'end_time' => $cart->formatted_end_time,
                'duration' => $cart->duration . ' jam',
                'total_price' => 'Rp ' . number_format($cart->total_price, 0, ',', '.'),
            ]
        ], 201);
    }

    /**
     * Update cart item
     */
    public function update(Request $request, Cart $cart): JsonResponse
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
            'merchant_id' => 'sometimes|required|exists:merchants,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'start' => 'sometimes|required|integer|min:0|max:23|no_booking_conflict',
            'quantity' => 'sometimes|required|integer|min:1|max:12',
        ];

        // Custom pesan error per field
        $messages = [
            'merchant_id.required' => 'Merchant wajib diisi.',
            'merchant_id.exists' => 'Merchant tidak ditemukan.',
            'product_id.required' => 'Lapangan wajib diisi.',
            'product_id.exists' => 'Lapangan tidak ditemukan.',
            'start.required' => 'Jam mulai wajib diisi.',
            'start.integer' => 'Jam mulai harus berupa angka.',
            'start.min' => 'Jam mulai minimal 0.',
            'start.max' => 'Jam mulai maksimal 23.',
            'start.no_booking_conflict' => 'Jam yang dipilih sudah dibooking.',
            'quantity.required' => 'Durasi wajib diisi.',
            'quantity.integer' => 'Durasi harus berupa angka.',
            'quantity.min' => 'Durasi minimal 1 jam.',
            'quantity.max' => 'Durasi maksimal 12 jam.',
        ];

        // Jalankan validasi manual
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray(),
                'hint' => 'Periksa kembali data yang dikirim'
            ], 422);
        }

        $validated = $validator->validated();

        // Update price per hour jika product berubah
        if (isset($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            $validated['price_per_hour'] = $product->price;
        }

        $cart->update($validated);

        // Reload cart dengan relationships
        $cart->load(['product', 'merchant']);

        return response()->json([
            'success' => true,
            'message' => 'Cart berhasil diperbarui',
            'cart' => $cart,
            'cart_info' => [
                'field_name' => $cart->product->name,
                'start_time' => $cart->formatted_start_time,
                'end_time' => $cart->formatted_end_time,
                'duration' => $cart->duration . ' jam',
                'total_price' => 'Rp ' . number_format($cart->total_price, 0, ',', '.'),
            ]
        ]);
    }

    /**
     * Get user's cart
     */
    public function index(): JsonResponse
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'merchant'])
            ->get();

        $totalPrice = $cartItems->sum('total_price');

        return response()->json([
            'cart_items' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'field_name' => $item->product->name,
                    'merchant_name' => $item->merchant->name,
                    'start_time' => $item->formatted_start_time,
                    'end_time' => $item->formatted_end_time,
                    'duration' => $item->duration . ' jam',
                    'price_per_hour' => 'Rp ' . number_format($item->price_per_hour, 0, ',', '.'),
                    'total_price' => 'Rp ' . number_format($item->total_price, 0, ',', '.'),
                ];
            }),
            'total_price' => 'Rp ' . number_format($totalPrice, 0, ',', '.'),
            'item_count' => $cartItems->count(),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Cart $cart): JsonResponse
    {
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari cart'
        ]);
    }

    /**
     * Clear user's cart
     */
    public function clear(): JsonResponse
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart berhasil dikosongkan'
        ]);
    }
}
