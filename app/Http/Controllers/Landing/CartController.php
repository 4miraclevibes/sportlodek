<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display the cart page
     */
    public function index()
    {
        // Ambil cart items untuk user yang login
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['merchant', 'product'])
            ->get();

        return view('pages.landing.cart', compact('cartItems'));
    }
}
