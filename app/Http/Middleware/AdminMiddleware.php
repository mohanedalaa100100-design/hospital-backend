<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // طالما الروت عليه auth:sanctum
        // يبقى المستخدم مسجل دخول بالفعل
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، هذه الصلاحية للمسؤولين (Admins) فقط.'
            ], 403);
        }

        return $next($request);
    }
}