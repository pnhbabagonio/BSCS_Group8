<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\Requirement;
use App\Models\SupportTicket; // Add this import
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real user/member data
        $totalMembers = User::count();
        $activeMembers = User::active()->count();
        
        // Real financial data from payments
        $totalBalance = Payment::where('status', 'paid')->sum('amount_paid');
        $membershipFees = $this->getMembershipFees();
        $supportTickets = SupportTicket::count(); // Change from monthlyExpenses to supportTickets
        
        $financialSummary = [
            'totalBalance' => $totalBalance,
            'membershipFees' => $membershipFees,
            'supportTickets' => $supportTickets, // Change key name
            'totalMembers' => $totalMembers,
        ];

        // Real recent transactions
        $recentTransactions = Payment::with(['user', 'requirement'])
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'description' => $payment->requirement ? $payment->requirement->title . ' - ' . $payment->getDisplayNameAttribute() : 'Payment - ' . $payment->getDisplayNameAttribute(),
                    'amount' => (float) $payment->amount_paid,
                    'date' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : 'N/A',
                    'type' => $payment->requirement ? strtolower($payment->requirement->title) : 'payment',
                    'method' => $payment->payment_method ?: 'QR Code',
                ];
            })->toArray();

        // Real QR Analytics (using payment data)
        $qrAnalytics = $this->getQrAnalytics();

        // Real engagement data
        $engagementData = [
            'activeMembers' => $activeMembers,
            'eventAttendance' => $this->calculateEventAttendance(),
            'paymentCompliance' => $this->calculatePaymentCompliance(),
            'platformUsage' => $this->calculatePlatformUsage(),
        ];

        // Real calendar events from Event model
        $calendarEvents = Event::where('date', '>=', now()->startOfMonth())
            ->where('date', '<=', now()->endOfMonth())
            ->get()
            ->map(function ($event) {
                return [
                    'date' => $event->date->day,
                    'title' => $event->title,
                    'type' => strtolower($event->category),
                ];
            })->toArray();

        // Real announcements (you can create an Announcement model later)
        $announcements = [
            ['id' => 1, 'title' => 'Payment System Live', 'content' => 'QR-based payment system is now fully operational with ' . Payment::count() . ' transactions processed.', 'date' => now()->format('Y-m-d'), 'priority' => 'high'],
            ['id' => 2, 'title' => 'Member Statistics', 'content' => 'Currently have ' . $totalMembers . ' total members with ' . $activeMembers . ' active members.', 'date' => now()->subDay()->format('Y-m-d'), 'priority' => 'medium'],
            ['id' => 3, 'title' => 'Event Management', 'content' => Event::count() . ' events scheduled. ' . Event::upcoming()->count() . ' upcoming events.', 'date' => now()->subDays(2)->format('Y-m-d'), 'priority' => 'low'],
        ];

        return Inertia::render('Dashboard', [
            'financialSummary' => $financialSummary,
            'recentTransactions' => $recentTransactions,
            'qrAnalytics' => $qrAnalytics,
            'engagementData' => $engagementData,
            'calendarEvents' => $calendarEvents,
            'announcements' => $announcements,
            'notificationCount' => $this->getNotificationCount(),
        ]);
    }

    /**
     * Calculate membership fees from requirements
     */
    private function getMembershipFees()
    {
        return Requirement::where('title', 'like', '%membership%')
            ->orWhere('title', 'like', '%Membership%')
            ->get()
            ->sum(function ($requirement) {
                return $requirement->payments()->where('status', 'paid')->sum('amount_paid');
            });
    }

    /**
     * Remove the getMonthlyExpenses method since we're replacing it with support tickets count
     */
    
    /**
     * Calculate QR analytics from payment data
     */
    private function getQrAnalytics()
    {
        $totalPayments = Payment::count();
        $successfulPayments = Payment::where('status', 'paid')->count();
        $failedPayments = Payment::where('status', 'unpaid')->count();
        $averagePayment = Payment::where('status', 'paid')->avg('amount_paid') ?: 0;

        return [
            'totalScans' => $totalPayments,
            'successfulPayments' => $successfulPayments,
            'failedScans' => $failedPayments,
            'averagePaymentAmount' => (float) $averagePayment,
        ];
    }

    /**
     * Calculate event attendance percentage
     */
    private function calculateEventAttendance()
    {
        $totalEvents = Event::count();
        if ($totalEvents === 0) return 0;

        $eventsWithAttendees = Event::has('attendees')->count();
        return round(($eventsWithAttendees / $totalEvents) * 100, 1);
    }

    /**
     * Calculate payment compliance percentage
     */
    private function calculatePaymentCompliance()
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) return 0;

        $usersWithPayments = Payment::where('status', 'paid')
            ->distinct('user_id')
            ->count('user_id');

        return round(($usersWithPayments / $totalUsers) * 100, 1);
    }

    /**
     * Calculate platform usage percentage based on recent logins
     */
    private function calculatePlatformUsage()
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) return 0;

        $recentlyActiveUsers = User::where('last_login', '>=', Carbon::now()->subDays(30))->count();
        
        return round(($recentlyActiveUsers / $totalUsers) * 100, 1);
    }

    /**
     * Get notification count (placeholder - implement your notification system)
     */
    private function getNotificationCount()
    {
        // Placeholder - implement based on your notification system
        $pendingPayments = Payment::where('status', 'pending')->count();
        $upcomingEvents = Event::upcoming()->count();
        
        return $pendingPayments + $upcomingEvents;
    }

    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        return response()->json([
            'total_members' => User::count(),
            'active_members' => User::active()->count(),
            'student_members' => User::role('student')->count(),
            'admin_members' => User::role('admin')->count(),
            'recent_registrations' => User::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'total_payments' => Payment::count(),
            'total_payment_amount' => (float) Payment::where('status', 'paid')->sum('amount_paid'),
            'upcoming_events' => Event::upcoming()->count(),
        ]);
    }
}