<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.profile')->with('error', 'Anda belum memiliki merchant profile');
        }

        $payments = Payment::where('merchant_id', $merchant->id)
            ->with(['transaction', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.merchant.payments', compact('payments'));
    }
}
