<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Store a new product (field)
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

        // Cek apakah user adalah merchant
        $user = Auth::user();
        if (!$user->merchant) {
            return response()->json([
                'message' => 'Akses ditolak: Hanya merchant yang dapat menambah lapangan.',
                'error' => 'UNAUTHORIZED',
                'hint' => 'Anda harus menjadi merchant untuk mengakses fitur ini'
            ], 403);
        }

        // Ambil merchant_id dari user yang login
        $merchant = $user->merchant;
        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant tidak ditemukan.',
                'error' => 'MERCHANT_NOT_FOUND',
                'hint' => 'Data merchant tidak ditemukan'
            ], 404);
        }

        // Definisikan rules validasi
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ];

        // Custom pesan error per field
        $messages = [
            'name.required' => 'Nama lapangan wajib diisi.',
            'name.string' => 'Nama lapangan harus berupa teks.',
            'name.max' => 'Nama lapangan maksimal 255 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
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

        // Tambahkan merchant_id dari user yang login
        $data = $validator->validated();
        $data['merchant_id'] = $merchant->id;

        $product = Product::create($data);

        return response()->json([
            'message' => 'Lapangan berhasil ditambahkan',
            'data' => $product->load('merchant')
        ], 201);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        // Cek dulu apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Akses ditolak: Anda belum login atau token tidak valid.',
                'error' => 'UNAUTHENTICATED',
                'hint' => 'Pastikan sudah login dan mengirimkan Authorization: Bearer <token>'
            ], 401);
        }

        // Cek apakah user adalah merchant
        $user = Auth::user();
        if (!$user->merchant) {
            return response()->json([
                'message' => 'Akses ditolak: Hanya merchant yang dapat mengedit lapangan.',
                'error' => 'UNAUTHORIZED',
                'hint' => 'Anda harus menjadi merchant untuk mengakses fitur ini'
            ], 403);
        }

        // Cek apakah product milik merchant yang login
        if ($product->merchant_id !== $user->merchant->id) {
            return response()->json([
                'message' => 'Akses ditolak: Anda hanya dapat mengedit lapangan Anda sendiri.',
                'error' => 'UNAUTHORIZED',
                'hint' => 'Lapangan ini bukan milik Anda'
            ], 403);
        }

        // Definisikan rules validasi
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0',
        ];

        // Custom pesan error per field
        $messages = [
            'name.required' => 'Nama lapangan wajib diisi.',
            'name.string' => 'Nama lapangan harus berupa teks.',
            'name.max' => 'Nama lapangan maksimal 255 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
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

        $product->update($validator->validated());

        return response()->json([
            'message' => 'Lapangan berhasil diperbarui',
            'data' => $product->load('merchant')
        ]);
    }

    /**
     * Get products by merchant
     */
    public function getByMerchant(Merchant $merchant): JsonResponse
    {
        $products = $merchant->products()->get();

        return response()->json([
            'merchant' => $merchant,
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->formatted_price,
                    'price_raw' => $product->price,
                ];
            })
        ]);
    }

    /**
     * Delete the specified product
     */
    public function destroy(Product $product): JsonResponse
    {
        // Cek dulu apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Akses ditolak: Anda belum login atau token tidak valid.',
                'error' => 'UNAUTHENTICATED',
                'hint' => 'Pastikan sudah login dan mengirimkan Authorization: Bearer <token>'
            ], 401);
        }

        // Cek apakah user adalah merchant
        $user = Auth::user();
        if (!$user->merchant) {
            return response()->json([
                'message' => 'Akses ditolak: Hanya merchant yang dapat menghapus lapangan.',
                'error' => 'UNAUTHORIZED',
                'hint' => 'Anda harus menjadi merchant untuk mengakses fitur ini'
            ], 403);
        }

        // Cek apakah product milik merchant yang login
        if ($product->merchant_id !== $user->merchant->id) {
            return response()->json([
                'message' => 'Akses ditolak: Anda hanya dapat menghapus lapangan Anda sendiri.',
                'error' => 'UNAUTHORIZED',
                'hint' => 'Lapangan ini bukan milik Anda'
            ], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Lapangan berhasil dihapus'
        ]);
    }

    /**
     * Get product details with availability
     */
    public function show(Product $product): JsonResponse
    {
        $merchant = $product->merchant;

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->formatted_price,
                'price_raw' => $product->price,
            ],
            'merchant' => [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'address' => $merchant->address,
                'phone' => $merchant->phone,
                'operational_hours' => [
                    'open' => sprintf('%02d:00', $merchant->open),
                    'close' => sprintf('%02d:00', $merchant->close),
                ]
            ]
        ]);
    }

}
