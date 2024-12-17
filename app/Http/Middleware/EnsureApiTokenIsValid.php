<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('API-Token');

        if ($token !== env('API_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
