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

        // Definisikan rules validasi
        $rules = [
            'merchant_id' => 'required|exists:merchants,id',
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ];

        // Custom pesan error per field
        $messages = [
            'merchant_id.required' => 'Merchant wajib diisi.',
            'merchant_id.exists' => 'Merchant tidak ditemukan.',
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

        $product = Product::create($validator->validated());

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

        // Definisikan rules validasi
        $rules = [
            'merchant_id' => 'sometimes|required|exists:merchants,id',
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0',
        ];

        // Custom pesan error per field
        $messages = [
            'merchant_id.required' => 'Merchant wajib diisi.',
            'merchant_id.exists' => 'Merchant tidak ditemukan.',
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
