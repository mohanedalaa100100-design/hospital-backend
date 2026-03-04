<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. نشيك هل اليوزر مسجل دخول أصلاً؟
        // 2. نشيك هل قيمة is_admin في الجدول بتساوي 1 (true)؟
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request); // اتفضل يا باشا ادخل
        }

        // لو مش أدمن، ارمي له Error 403 (Forbidden)
        return response()->json([
            'status' => false,
            'message' => 'عذراً، هذه الصلاحية للمسؤولين (Admins) فقط.'
        ], 403);
    }
}