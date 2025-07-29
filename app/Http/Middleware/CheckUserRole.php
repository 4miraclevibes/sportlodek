<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Check if user is merchant
        if ($user->isMerchant()) {
            // If merchant tries to access user routes, redirect to merchant dashboard
            if ($request->is('/') || $request->is('/cart') || $request->is('/transaction') || $request->is('/profile-mobile')) {
                return redirect()->route('merchant.dashboard');
            }
        } else {
            // If regular user tries to access merchant routes, redirect to landing page
            if ($request->is('/merchant*')) {
                return redirect()->route('welcome');
            }
        }

        return $next($request);
    }
}
