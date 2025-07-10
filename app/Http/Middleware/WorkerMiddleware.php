<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class WorkerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'worker') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. Only workers can access this route.'], 403);
    }
}
