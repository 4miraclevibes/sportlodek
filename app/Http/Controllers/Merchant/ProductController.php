<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.profile')->with('error', 'Anda belum memiliki merchant profile');
        }

        $products = Product::where('merchant_id', $merchant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.merchant.products', compact('products'));
    }

    /**
     * Store a new product
     */
    public function store(Request $request)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Anda belum memiliki merchant profile',
                'error' => 'MERCHANT_NOT_FOUND'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ], [
            'name.required' => 'Nama lapangan wajib diisi.',
            'name.string' => 'Nama lapangan harus berupa teks.',
            'name.max' => 'Nama lapangan maksimal 255 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $product = Product::create([
            'merchant_id' => $merchant->id,
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return response()->json([
            'message' => 'Lapangan berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Anda belum memiliki merchant profile',
                'error' => 'MERCHANT_NOT_FOUND'
            ], 400);
        }

        // Check if product belongs to this merchant
        if ($product->merchant_id !== $merchant->id) {
            return response()->json([
                'message' => 'Lapangan tidak ditemukan',
                'error' => 'PRODUCT_NOT_FOUND'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ], [
            'name.required' => 'Nama lapangan wajib diisi.',
            'name.string' => 'Nama lapangan harus berupa teks.',
            'name.max' => 'Nama lapangan maksimal 255 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return response()->json([
            'message' => 'Lapangan berhasil diperbarui',
            'data' => $product
        ]);
    }

    /**
     * Delete the specified product
     */
    public function destroy(Product $product)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Anda belum memiliki merchant profile',
                'error' => 'MERCHANT_NOT_FOUND'
            ], 400);
        }

        // Check if product belongs to this merchant
        if ($product->merchant_id !== $merchant->id) {
            return response()->json([
                'message' => 'Lapangan tidak ditemukan',
                'error' => 'PRODUCT_NOT_FOUND'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Lapangan berhasil dihapus'
        ]);
    }

    /**
     * Get product details for editing
     */
    public function show(Product $product)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Anda belum memiliki merchant profile',
                'error' => 'MERCHANT_NOT_FOUND'
            ], 400);
        }

        // Check if product belongs to this merchant
        if ($product->merchant_id !== $merchant->id) {
            return response()->json([
                'message' => 'Lapangan tidak ditemukan',
                'error' => 'PRODUCT_NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'data' => $product
        ]);
    }
}
