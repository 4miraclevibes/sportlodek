<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.profile')->with('error', 'Anda belum memiliki merchant profile');
        }

        $transactions = Transaction::where('merchant_id', $merchant->id)
            ->with(['user', 'merchant', 'transactionDetails.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.merchant.transactions', compact('transactions'));
    }
}
