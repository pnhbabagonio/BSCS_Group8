<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }
        
        // Allow only Admins
        if ($user->role !== 'Admin') {
            return response()->json([
                'message' => 'Access restricted to administrators only.'
            ], 403);
        }

        // Check if account is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not active.'
            ], 403);
        }

        return $next($request);
    }
}