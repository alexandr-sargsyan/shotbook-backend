<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isEmailVerified()) {
            return response()->json([
                'message' => 'Email не подтвержден. Пожалуйста, подтвердите email перед доступом к этому ресурсу.',
            ], 403);
        }

        return $next($request);
    }
}
