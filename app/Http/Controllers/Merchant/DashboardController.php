<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.profile')->with('error', 'Anda belum memiliki merchant profile');
        }

        // Get statistics
        $totalBookings = Transaction::where('merchant_id', $merchant->id)->count();
        $totalRevenue = Transaction::where('merchant_id', $merchant->id)
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');
        $totalProducts = Product::where('merchant_id', $merchant->id)->count();
        $pendingBookings = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'pending')
            ->count();

        // Get recent transactions
        $recentTransactions = Transaction::where('merchant_id', $merchant->id)
            ->with(['user', 'merchant', 'transactionDetails.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.merchant.dashboard', compact(
            'totalBookings',
            'totalRevenue',
            'totalProducts',
            'pendingBookings',
            'recentTransactions'
        ));
    }
}
