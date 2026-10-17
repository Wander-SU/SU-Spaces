<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()?->role->role_name==="System Admin") {
            return response()->json(['message' => 'Administrator access required.'], 403);
        }
        // dd($request->user()?->role()->role_name);
        return $next($request);
    }
}
