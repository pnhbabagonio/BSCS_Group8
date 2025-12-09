<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MemberAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Allow only PSITS-NEXUS members (Members, Officers, Admins)
        if (!$user || !in_array($user->role, ['Member', 'Officer', 'Admin'])) {
            return response()->json([
                'message' => 'Access restricted to PSITS-NEXUS members only.'
            ], 403);
        }

        // Check if account is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not active. Please contact administrator.'
            ], 403);
        }

        return $next($request);
    }
}