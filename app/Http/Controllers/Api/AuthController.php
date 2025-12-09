<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login via API
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is a PSITS-NEXUS member (any role)
        if (!in_array($user->role, ['Member', 'Officer', 'Admin'])) {
            return response()->json([
                'message' => 'Access restricted to PSITS-NEXUS members only.'
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not active. Please contact administrator.'
            ], 403);
        }

        // Update last login
        $user->update(['last_login' => now()]);

        // Create token with device name
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $user->student_id,
                'program' => $user->program,
                'year' => $user->year,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout via API (revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        // Return limited user info
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $user->student_id,
                'program' => $user->program,
                'year' => $user->year,
                'role' => $user->role,
                'status' => $user->status,
            ]
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $request->user()->tokens()->delete();
        
        $newToken = $request->user()->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $newToken,
            'token_type' => 'Bearer',
        ]);
    }
}