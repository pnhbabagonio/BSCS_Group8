<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpSupportController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');




// Forgot password (request reset link)
Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Reset password (form + store new password)
Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

// Replace the dashboard closure with controller
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Add API route for dashboard stats
Route::get('dashboard/stats', [DashboardController::class, 'getStats'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.stats');

Route::get('financial-reports', function () {
    return Inertia::render('FinancialReports');
})->middleware(['auth', 'verified'])->name('financial-reports');

Route::get('expenses-tracking', function () {
    return Inertia::render('ExpenseTracking');
})->middleware(['auth', 'verified'])->name('expenses-tracking');

// User management routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('user-management', [UserController::class, 'index'])->name('user-management');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Event routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('Events');
    Route::get('/events/data', [EventController::class, 'data'])->name('events.data');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::patch('/events/{event}/registration', [EventController::class, 'updateRegistration'])->name('events.registration.update');

    // Add these registration routes
    Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::delete('/events/{event}/register', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
    Route::get('/events/{event}/registrants', [RegistrationController::class, 'getRegistrants'])->name('registrations.registrants');
    Route::delete('/events/{event}/registrants/{registration}', [RegistrationController::class, 'removeRegistrant'])->name('registrations.remove');
});


// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Event management
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // User-specific event routes
    Route::get('/my-events', [EventController::class, 'myEvents'])->name('events.my');
    Route::get('/registered-events', [EventController::class, 'registeredEvents'])->name('events.registered');

    // Registration routes
    Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::delete('/events/{event}/register', [RegistrationController::class, 'destroy'])->name('registrations.destroy');

    // Admin registration management
    Route::get('/events/{event}/registrants', [RegistrationController::class, 'getRegistrants'])->name('registrations.registrants');
    Route::delete('/events/{event}/registrants/{registration}', [RegistrationController::class, 'removeRegistrant'])->name('registrations.remove');
    Route::post('/events/{event}/registrants/{registration}/attendance', [RegistrationController::class, 'updateAttendance'])->name('registrations.attendance');
    Route::post('/events/{event}/registrants/{registration}/promote', [RegistrationController::class, 'promoteFromWaitlist'])->name('registrations.promote');
});
// Attendee Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendees', [AttendeeController::class, 'index'])->name('attendees');
    Route::post('/attendees', [AttendeeController::class, 'store'])->name('attendees.store');
    Route::get('/attendees/export', [AttendeeController::class, 'export'])->name('attendees.export');
});

// Event Analytics Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
});

// Payment Management Page
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/payment', function () {
        return Inertia::render('Payment'); // matches resources/js/Pages/Payment.vue
    })->name('payment.index');

    // Route::get('/records', function () {
    //     return Inertia::render('Records');
    // })->name('records.index');

    Route::get('/profiles', function () {
        return Inertia::render('Profiles');
    })->name('profiles.index');
});

// --------------------
// Payments Module
// --------------------
Route::middleware(['auth', 'verified'])->prefix('payments')->name('payments.')->group(function () {
    Route::get('/qr-generator', function () {
        return Inertia::render('QrGenerator');
    })->name('qr-generator');

    Route::get('/record', function () {
        return Inertia::render('RecordPayment');
    })->name('record');

    Route::post('/record', [PaymentController::class, 'record'])->name('record.store');
});

// ✅ Transactions Module
Route::middleware(['auth', 'verified'])->group(function () {
    // FIXED: The controller now handles rendering the page AND the data logic.
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // Transaction Exports
    Route::get('/transactions/export/csv', [TransactionController::class, 'exportCsv'])->name('transactions.export.csv');
    Route::get('/transactions/export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');

    // Receipt download
    Route::get('/transactions/{id}/receipt', [TransactionController::class, 'receipt'])
        ->name('transactions.receipt');
});


// Reports Page
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports', function () {
        return Inertia::render('Reports'); // matches resources/js/Pages/Reports.vue
    })->name('reports');

    // API endpoint for reports data
    Route::get('/reports/stats', [\App\Http\Controllers\ReportController::class, 'index'])
        ->name('reports.stats');
});

// Help & Support routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('help-support', [HelpSupportController::class, 'index'])->name('help-support');
    Route::post('help-support', [HelpSupportController::class, 'store'])->name('help-support.store');
});

// ChatBot routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('ChatBot', [ChatbotController::class, 'index'])->name('ChatBot');
    Route::post('ChatBot', [ChatbotController::class, 'store'])->name('ChatBot.store');
});

// Platform Guide page
Route::get('platform-guide', function () {
    return Inertia::render('PlatformGuide');
    // This should match resources/js/Pages/PlatformGuide.vue
})->middleware(['auth', 'verified'])->name('platform-guide');

// ✅ Registration Pending Page (Added)
Route::get('/registration-pending', function () {
    return Inertia::render('auth/RegistrationPending');
})->name('registration.pending');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
