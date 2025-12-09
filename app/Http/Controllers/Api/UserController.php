<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users (for API)
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'student_id', 'program', 'year', 'role', 'status')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Get specific user
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        return response()->json([
            'user' => $user
        ]);
    }
}