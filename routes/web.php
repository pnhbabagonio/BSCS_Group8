<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpSupportController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserProfileController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Public Contact Support
|--------------------------------------------------------------------------
*/
Route::get('/contact-support', [HelpSupportController::class, 'publicContact'])->name('contact-support');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
});

/*
|--------------------------------------------------------------------------
| User Management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user-management', [UserController::class, 'index'])->name('user-management');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

/*
|--------------------------------------------------------------------------
| Financial Management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/export/csv', [TransactionController::class, 'exportCsv'])->name('transactions.export.csv');
    Route::get('/transactions/export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
    Route::get('/transactions/{id}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');

    // Financial Reports
    Route::get('/financial-reports', fn() => Inertia::render('FinancialReports'))->name('financial-reports');

    // Expenses Tracking
    Route::get('/expenses-tracking', fn() => Inertia::render('ExpenseTracking'))->name('expenses-tracking');
});

/*
|--------------------------------------------------------------------------
| Event Management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Main Event Management Page - Use EventController to load data
    Route::get('/event-management', [EventController::class, 'index'])->name('event-management');

    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    // Manual Registration Routes
    Route::get('/user-management-data', [UserController::class, 'getUsersForRegistration'])->name('user-management.data');
    Route::get('/events/{event}/attendees', [EventController::class, 'getEventAttendees'])->name('events.attendees');
    Route::post('/events/{event}/register-attendees', [EventController::class, 'registerAttendees'])->name('events.register-attendees');
});



/*
|--------------------------------------------------------------------------
| Payment Management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Main payment page with tabs
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');

    // Requirements CRUD routes
    Route::get('/requirements', [RequirementController::class, 'index'])->name('requirements.index');
    Route::post('/requirements', [RequirementController::class, 'store'])->name('requirements.store');
    Route::put('/requirements/{requirement}', [RequirementController::class, 'update'])->name('requirements.update');
    Route::delete('/requirements/{requirement}', [RequirementController::class, 'destroy'])->name('requirements.destroy');

    // Payment Records CRUD routes
    Route::get('/records', [PaymentController::class, 'index'])->name('records.index');
    Route::post('/records', [PaymentController::class, 'store'])->name('records.store');
    Route::put('/records/{payment}', [PaymentController::class, 'update'])->name('records.update');
    Route::delete('/records/{payment}', [PaymentController::class, 'destroy'])->name('records.destroy');

    // Additional payment routes
    Route::get('/records/stats', [PaymentController::class, 'getStats'])->name('records.stats');
    Route::get('/records/search', [PaymentController::class, 'search'])->name('records.search');

    // User Profiles routes - ADD THESE NEW ROUTES
    Route::get('/user-profiles', [UserProfileController::class, 'index'])->name('user-profiles.index');
    Route::get('/user-profiles/{id}', [UserProfileController::class, 'show'])->name('user-profiles.show');
});

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports', fn() => Inertia::render('Reports'))->name('reports');
    Route::get('/reports/stats', [ReportController::class, 'index'])->name('reports.stats');
});

/*
|--------------------------------------------------------------------------
| Help & Support
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/help-support', [HelpSupportController::class, 'index'])->name('help-support');
    Route::post('/help-support', [HelpSupportController::class, 'store'])->name('help-support.store');
    // Add this line for the tickets page
    Route::get('/help-support/tickets', [HelpSupportController::class, 'tickets'])->name('help-support.tickets');
});

/*
|--------------------------------------------------------------------------
| ChatBot
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/ChatBot', [ChatbotController::class, 'index'])->name('ChatBot');
    Route::post('/ChatBot', [ChatbotController::class, 'store'])->name('ChatBot.store');
});

/*
|--------------------------------------------------------------------------
| Platform Guide
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/platform-guide', fn() => Inertia::render('PlatformGuide'))->name('platform-guide');
});

/*
|--------------------------------------------------------------------------
| Registration Pending
|--------------------------------------------------------------------------
*/
Route::get('/registration-pending', fn() => Inertia::render('auth/RegistrationPending'))->name('registration.pending');

/*
|--------------------------------------------------------------------------
| Extra Files
|--------------------------------------------------------------------------
*/
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| API Testing Page
|--------------------------------------------------------------------------
*/
Route::get('/api-test', function () {
    return view('api-test');
});
