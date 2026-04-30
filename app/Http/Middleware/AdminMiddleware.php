<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    
    public function handle(Request $request, Closure $next): Response
    {
        
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'عذراً، هذه الصلاحية للمسؤولين (Admins) فقط.'
            ], 403, [], JSON_UNESCAPED_SLASHES);
        }

        return $next($request);
    }
}