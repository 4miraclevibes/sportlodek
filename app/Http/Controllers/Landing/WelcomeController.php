<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\User;
use App\Models\TransactionDetail;

class WelcomeController extends Controller
{
    public function index()
    {
        $featuredMerchants = Merchant::where('status', 'active')
            ->with(['products' => function($query) {
                $query->where('price', '>', 0);
            }])
            ->take(8)
            ->get();

        $totalMerchants = Merchant::where('status', 'active')->count();
        $totalProducts = Product::count();
        $totalUsers = User::count();

        return view('pages.landing.welcome', compact(
            'featuredMerchants',
            'totalMerchants',
            'totalProducts',
            'totalUsers'
        ));
    }

    /**
     * Get merchant details with products and availability
     */
    public function getMerchantDetails($merchantId)
    {
        try {
            $merchant = Merchant::with(['products' => function($query) {
                $query->where('price', '>', 0);
            }])->findOrFail($merchantId);

            // Get booked hours for each product
            $products = $merchant->products->map(function($product) use ($merchant) {
                $bookedHours = TransactionDetail::where('product_id', $product->id)
                    ->whereHas('transaction', function($q) {
                        $q->where('status', '!=', 'cancelled');
                    })
                    ->pluck('hour')
                    ->toArray();

                // Generate available hours based on merchant operational hours
                $operationalHours = range($merchant->open, $merchant->close - 1);
                $availableHours = array_values(array_diff($operationalHours, $bookedHours));

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'formatted_price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                    'booked_hours' => $bookedHours,
                    'available_hours' => $availableHours,
                    'operational_hours' => $operationalHours,
                    'total_operational_hours' => count($operationalHours),
                    'total_available_hours' => count($availableHours)
                ];
            });

            return response()->json([
                'merchant' => [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'address' => $merchant->address,
                    'phone' => $merchant->phone,
                    'open' => $merchant->open,
                    'close' => $merchant->close,
                    'status' => $merchant->status,
                    'operational_hours_text' => sprintf('%02d:00 - %02d:00', $merchant->open, $merchant->close)
                ],
                'products' => $products,
                'total_products' => $products->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Merchant tidak ditemukan',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getAllMerchants()
    {
        $merchants = Merchant::where('status', 'active')
            ->with(['products' => function($query) {
                $query->where('price', '>', 0);
            }])
            ->get()
            ->map(function($merchant) {
                return [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'address' => $merchant->address,
                    'phone' => $merchant->phone,
                    'open' => $merchant->open,
                    'close' => $merchant->close,
                    'status' => $merchant->status,
                    'products_count' => $merchant->products->count()
                ];
            });

        return response()->json([
            'merchants' => $merchants
        ]);
    }
}
