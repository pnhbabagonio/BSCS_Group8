<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SupportTicketController;

// Public API routes
Route::get('/hello', function () {
    return response()->json(['message' => 'PSITS-NEXUS API is working!']);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Member-only API routes (require authentication and member role)
Route::middleware(['auth:sanctum', 'member.access'])->group(function () {
    // Member dashboard and profile
    Route::prefix('member')->group(function () {
        Route::get('/profile', [MemberController::class, 'profile']);
        Route::get('/dashboard', [MemberController::class, 'dashboard']);
        Route::get('/payments', [MemberController::class, 'payments']);
        Route::get('/requirements', [MemberController::class, 'requirements']);
        Route::get('/events', [MemberController::class, 'events']);
    });
    
    // Public events (members can view)
    Route::get('/events', [EventController::class, 'index']);

    // Support Tickets
    Route::prefix('support-tickets')->group(function () {
        Route::post('/', [SupportTicketController::class, 'store']);
        Route::get('/', [SupportTicketController::class, 'index']);
        Route::get('/{ticket}', [SupportTicketController::class, 'show']);
    });
    
});

// Admin/Officer only routes (for future use)
Route::middleware(['auth:sanctum', 'admin.access'])->group(function () {
    // Admin routes here
});

// Protected test endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/protected-test', function () {
        return response()->json(['message' => 'This is a protected endpoint!']);
    });
});