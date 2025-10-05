<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Requirement;
use Inertia\Inertia;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * Display a listing of user payment profiles
     */
    public function index(Request $request)
    {
        // Get users who have payment records (either as registered users or manual entries)
        $usersQuery = User::whereHas('payments')
            ->orWhereHas('manualPayments') // We'll add this relationship to User model
            ->with(['payments.requirement', 'paidRequirements', 'unpaidRequirements'])
            ->active();

        // Search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $usersQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('program', 'like', "%{$search}%");
            });
        }

        // Course/Program filter
        if ($request->has('course') && $request->course !== 'All') {
            $usersQuery->where('program', $request->course);
        }

        $users = $usersQuery->get()->map(function ($user) {
            return $this->formatUserPaymentData($user);
        });

        // Also include manual payments (payments without user_id but with names)
        $manualPayments = Payment::whereNull('user_id')
            ->whereNotNull('first_name')
            ->get()
            ->groupBy('student_id')
            ->map(function ($payments, $studentId) {
                return $this->formatManualPaymentData($payments, $studentId);
            });

        $allProfiles = $users->merge($manualPayments)->values();

        // Get unique programs for filter
        $programs = User::active()->distinct()->pluck('program')->filter()->values();

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json([
                'users' => $allProfiles,
                'filters' => [
                    'search' => $request->search,
                    'course' => $request->course ?? 'All',
                    'programs' => $programs
                ]
            ]);
        }

        return Inertia::render('PaymentManagement', [
            'users' => $allProfiles,
            'filters' => [
                'search' => $request->search,
                'course' => $request->course ?? 'All',
                'programs' => $programs
            ]
        ]);
    }

    /**
     * Format user payment data for response
     */
    private function formatUserPaymentData(User $user)
    {
        // Get all payments for this user
        $payments = $user->payments()->with('requirement')->get();
        
        // Calculate totals
        $totalPaid = $payments->where('status', 'paid')->sum('amount_paid');
        $totalUnpaid = $payments->where('status', 'unpaid')->sum('amount_paid');
        $totalBalance = $totalUnpaid; // Since unpaid represents balance

        // Get paid requirements
        $paidRequirements = $user->paidRequirements()->get()->map(function ($requirement) use ($user) {
            $payment = $user->payments()
                ->where('requirement_id', $requirement->id)
                ->where('status', 'paid')
                ->first();
            
            return [
                'id' => $requirement->id,
                'title' => $requirement->title,
                'amount' => (float) $requirement->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : null,
                'payment_method' => $payment->payment_method,
            ];
        });

        // Get unpaid requirements (all requirements minus paid ones)
        $allRequirements = Requirement::all();
        $unpaidRequirements = $allRequirements->filter(function ($requirement) use ($paidRequirements) {
            return !$paidRequirements->contains('id', $requirement->id);
        })->map(function ($requirement) {
            $now = now();
            $deadline = $requirement->deadline;
            
            return [
                'id' => $requirement->id,
                'title' => $requirement->title,
                'amount' => (float) $requirement->amount,
                'overdue' => $deadline < $now,
                'deadline' => $deadline->format('Y-m-d'),
            ];
        });

        // Payment history (all payments with details)
        $paymentHistory = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'requirement_title' => $payment->requirement->title,
                'amount_paid' => (float) $payment->amount_paid,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : null,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'id' => $user->id,
            'type' => 'user',
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'student_id' => $user->student_id,
            'program' => $user->program,
            'year' => $user->year,
            'section' => '', // Add if you have section field
            'total_balance' => $totalBalance,
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'paid_requirements' => $paidRequirements,
            'unpaid_requirements' => $unpaidRequirements,
            'payment_history' => $paymentHistory,
            'paid_requirements_count' => $paidRequirements->count(),
            'unpaid_requirements_count' => $unpaidRequirements->count(),
        ];
    }

    /**
     * Format manual payment data for response
     */
    private function formatManualPaymentData($payments, $studentId)
    {
        $firstPayment = $payments->first();
        
        $totalPaid = $payments->where('status', 'paid')->sum('amount_paid');
        $totalUnpaid = $payments->where('status', 'unpaid')->sum('amount_paid');
        $totalBalance = $totalUnpaid;

        // Group payments by requirement
        $paidRequirements = $payments->where('status', 'paid')->map(function ($payment) {
            return [
                'id' => $payment->requirement_id,
                'title' => $payment->requirement->title,
                'amount' => (float) $payment->requirement->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : null,
                'payment_method' => $payment->payment_method,
            ];
        });

        // Get unpaid requirements (all requirements minus paid ones)
        $paidRequirementIds = $paidRequirements->pluck('id')->toArray();
        $unpaidRequirements = Requirement::whereNotIn('id', $paidRequirementIds)
            ->get()
            ->map(function ($requirement) {
                $now = now();
                $deadline = $requirement->deadline;
                
                return [
                    'id' => $requirement->id,
                    'title' => $requirement->title,
                    'amount' => (float) $requirement->amount,
                    'overdue' => $deadline < $now,
                    'deadline' => $deadline->format('Y-m-d'),
                ];
            });

        // Payment history
        $paymentHistory = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'requirement_title' => $payment->requirement->title,
                'amount_paid' => (float) $payment->amount_paid,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : null,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'id' => 'manual_' . $studentId,
            'type' => 'manual',
            'first_name' => $firstPayment->first_name,
            'middle_name' => $firstPayment->middle_name,
            'last_name' => $firstPayment->last_name,
            'full_name' => trim("{$firstPayment->first_name} {$firstPayment->middle_name} {$firstPayment->last_name}"),
            'student_id' => $studentId,
            'program' => 'Manual Entry',
            'year' => 'N/A',
            'section' => 'N/A',
            'total_balance' => $totalBalance,
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'paid_requirements' => $paidRequirements,
            'unpaid_requirements' => $unpaidRequirements,
            'payment_history' => $paymentHistory,
            'paid_requirements_count' => $paidRequirements->count(),
            'unpaid_requirements_count' => $unpaidRequirements->count(),
        ];
    }

    /**
     * Get detailed user payment profile
     */
    public function show($id)
    {
        if (str_starts_with($id, 'manual_')) {
            $studentId = str_replace('manual_', '', $id);
            $payments = Payment::where('student_id', $studentId)
                ->whereNull('user_id')
                ->with('requirement')
                ->get();
            
            $userData = $this->formatManualPaymentData($payments, $studentId);
        } else {
            $user = User::with(['payments.requirement', 'paidRequirements', 'unpaidRequirements'])
                ->findOrFail($id);
            $userData = $this->formatUserPaymentData($user);
        }

        return response()->json($userData);
    }
}