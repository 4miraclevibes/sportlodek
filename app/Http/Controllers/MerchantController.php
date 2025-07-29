<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MerchantController extends Controller
{
    /**
     * Store a newly created merchant
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'status' => 'required|string|in:active,inactive,pending',
            'open' => 'required|integer|min:0|max:24',
            'close' => 'required|integer|min:0|max:24|business_hours',
            'banner' => 'nullable|string|max:255',
        ];

        // Custom pesan error per field
        $messages = [
            'name.required' => 'Nama merchant wajib diisi.',
            'name.string' => 'Nama merchant harus berupa teks.',
            'name.max' => 'Nama merchant maksimal 255 karakter.',
            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus active, inactive, atau pending.',
            'open.required' => 'Jam buka wajib diisi.',
            'open.integer' => 'Jam buka harus berupa angka.',
            'open.min' => 'Jam buka minimal 0.',
            'open.max' => 'Jam buka maksimal 24.',
            'close.required' => 'Jam tutup wajib diisi.',
            'close.integer' => 'Jam tutup harus berupa angka.',
            'close.min' => 'Jam tutup minimal 0.',
            'close.max' => 'Jam tutup maksimal 24.',
            'close.business_hours' => 'Jam tutup harus lebih besar dari jam buka.',
            'banner.string' => 'Banner harus berupa teks.',
            'banner.max' => 'Banner maksimal 255 karakter.',
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

        // Set user_id dari user yang sedang login
        $validated = $validator->validated();
        $validated['user_id'] = Auth::id();

        $merchant = Merchant::create($validated);

        return response()->json([
            'message' => 'Merchant berhasil dibuat',
            'data' => $merchant
        ], 201);
    }

    /**
     * Update the specified merchant
     */
    public function update(Request $request, Merchant $merchant): JsonResponse
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
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'phone' => 'sometimes|required|string|max:20',
            'status' => 'sometimes|required|string|in:active,inactive,pending',
            'open' => 'sometimes|required|integer|min:0|max:24',
            'close' => 'sometimes|required|integer|min:0|max:24|business_hours',
            'banner' => 'sometimes|nullable|string|max:255',
        ];

        // Custom pesan error per field
        $messages = [
            'name.required' => 'Nama merchant wajib diisi.',
            'name.string' => 'Nama merchant harus berupa teks.',
            'name.max' => 'Nama merchant maksimal 255 karakter.',
            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus active, inactive, atau pending.',
            'open.required' => 'Jam buka wajib diisi.',
            'open.integer' => 'Jam buka harus berupa angka.',
            'open.min' => 'Jam buka minimal 0.',
            'open.max' => 'Jam buka maksimal 24.',
            'close.required' => 'Jam tutup wajib diisi.',
            'close.integer' => 'Jam tutup harus berupa angka.',
            'close.min' => 'Jam tutup minimal 0.',
            'close.max' => 'Jam tutup maksimal 24.',
            'close.business_hours' => 'Jam tutup harus lebih besar dari jam buka.',
            'banner.string' => 'Banner harus berupa teks.',
            'banner.max' => 'Banner maksimal 255 karakter.',
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

        $merchant->update($validator->validated());

        return response()->json([
            'message' => 'Merchant berhasil diperbarui',
            'data' => $merchant
        ]);
    }

    /**
     * Get validation rules for testing
     */
    public function getValidationRules(): JsonResponse
    {
        return response()->json([
            'rules' => Merchant::getValidationRules()
        ]);
    }
}
