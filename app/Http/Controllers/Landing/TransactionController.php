<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display the transaction history page
     */
    public function index()
    {
        // Ambil transaction history untuk user yang login
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['merchant', 'transactionDetails.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.landing.transaction', compact('transactions'));
    }
}
