<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Requirement;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    /**
     * Display a listing of user payment profiles
     */
    public function index(Request $request)
    {
        try {
            // Get all requirements first
            $allRequirements = Requirement::all();
            
            // Get registered users with their payments
            $registeredUsers = User::with(['payments.requirement'])
                ->where(function($query) {
                    $query->whereHas('payments')
                          ->orWhereHas('manualPayments');
                })
                ->active()
                ->get()
                ->map(function ($user) use ($allRequirements) {
                    return $this->formatUserPaymentData($user, $allRequirements);
                });

            // Get manual payments (payments without user_id)
            $manualPayments = Payment::whereNull('user_id')
                ->whereNotNull('first_name')
                ->with('requirement')
                ->get()
                ->groupBy(function($payment) {
                    return $payment->student_id ?: 'manual_' . $payment->id;
                })
                ->map(function ($payments, $groupId) use ($allRequirements) {
                    return $this->formatManualPaymentData($payments, $groupId, $allRequirements);
                });

            $allProfiles = $registeredUsers->merge($manualPayments)->values();

            // Convert to plain array to ensure proper JSON structure
            $allProfiles = collect($allProfiles)->map(function ($profile) {
                return $this->ensureArrayStructure($profile);
            })->toArray();

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
            
        } catch (\Exception $e) {
            Log::error('Error in UserProfileController@index: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to load user profiles'], 500);
            }
            
            return Inertia::render('PaymentManagement', [
                'users' => [],
                'filters' => [
                    'search' => $request->search,
                    'course' => $request->course ?? 'All',
                    'programs' => []
                ]
            ]);
        }
    }

    /**
     * Ensure all nested structures are proper arrays
     */
    private function ensureArrayStructure($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'ensureArrayStructure'], $data);
        }
        
        if (is_object($data)) {
            $data = (array) $data;
        }
        
        // Ensure specific fields are arrays
        $arrayFields = ['paid_requirements', 'unpaid_requirements', 'payment_history'];
        
        foreach ($arrayFields as $field) {
            if (isset($data[$field]) && !is_array($data[$field])) {
                if ($data[$field] instanceof Collection) {
                    $data[$field] = $data[$field]->toArray();
                } else {
                    $data[$field] = [];
                }
            }
        }
        
        return $data;
    }

    /**
     * Format user payment data for response
     */
    private function formatUserPaymentData(User $user, $allRequirements)
    {
        // Get all payments for this user (both direct and manual with same student_id)
        $directPayments = $user->payments()->with('requirement')->get();
        $manualPayments = $this->getManualPaymentsForUser($user);
        $allPayments = $directPayments->merge($manualPayments);

        // Calculate totals
        $totalPaid = $allPayments->where('status', 'paid')->sum('amount_paid');
        $totalUnpaid = $allPayments->whereIn('status', ['unpaid', 'pending'])->sum('amount_paid');
        $totalBalance = $totalUnpaid;

        // Get paid requirements from payments
        $paidRequirements = $allPayments->where('status', 'paid')->map(function ($payment) {
            return [
                'id' => $payment->requirement_id,
                'title' => $payment->requirement->title,
                'amount' => (float) $payment->requirement->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                'payment_method' => $payment->payment_method,
            ];
        })->values(); // Ensure it's a collection with proper indices

        // Get unpaid requirements (all requirements minus paid ones)
        $paidRequirementIds = $paidRequirements->pluck('id')->toArray();
        $unpaidRequirements = $allRequirements->whereNotIn('id', $paidRequirementIds)
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
            })->values(); // Ensure it's a collection with proper indices

        // Payment history (all payments with details)
        $paymentHistory = $allPayments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'requirement_title' => $payment->requirement->title,
                'amount_paid' => (float) $payment->amount_paid,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ];
        })->values(); // Ensure it's a collection with proper indices

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
            'section' => $user->section ?? '',
            'total_balance' => (float) $totalBalance,
            'total_paid' => (float) $totalPaid,
            'total_unpaid' => (float) $totalUnpaid,
            'paid_requirements' => $paidRequirements->toArray(),
            'unpaid_requirements' => $unpaidRequirements->toArray(),
            'payment_history' => $paymentHistory->toArray(),
            'paid_requirements_count' => $paidRequirements->count(),
            'unpaid_requirements_count' => $unpaidRequirements->count(),
        ];
    }

    /**
     * Get manual payments for a user (by student_id)
     */
    private function getManualPaymentsForUser(User $user)
    {
        if (!$user->student_id) {
            return collect();
        }
        
        return Payment::where('student_id', $user->student_id)
            ->whereNull('user_id')
            ->with('requirement')
            ->get();
    }

    /**
     * Format manual payment data for response
     */
    private function formatManualPaymentData($payments, $groupId, $allRequirements)
    {
        $firstPayment = $payments->first();
        
        $totalPaid = $payments->where('status', 'paid')->sum('amount_paid');
        $totalUnpaid = $payments->whereIn('status', ['unpaid', 'pending'])->sum('amount_paid');
        $totalBalance = $totalUnpaid;

        // Group payments by requirement for paid requirements
        $paidRequirements = $payments->where('status', 'paid')->map(function ($payment) {
            return [
                'id' => $payment->requirement_id,
                'title' => $payment->requirement->title,
                'amount' => (float) $payment->requirement->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                'payment_method' => $payment->payment_method,
            ];
        })->values();

        // Get unpaid requirements (all requirements minus paid ones)
        $paidRequirementIds = $paidRequirements->pluck('id')->toArray();
        $unpaidRequirements = $allRequirements->whereNotIn('id', $paidRequirementIds)
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
            })->values();

        // Payment history
        $paymentHistory = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'requirement_title' => $payment->requirement->title,
                'amount_paid' => (float) $payment->amount_paid,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ];
        })->values();

        return [
            'id' => 'manual_' . $groupId,
            'type' => 'manual',
            'first_name' => $firstPayment->first_name,
            'middle_name' => $firstPayment->middle_name,
            'last_name' => $firstPayment->last_name,
            'full_name' => trim("{$firstPayment->first_name} {$firstPayment->middle_name} {$firstPayment->last_name}"),
            'student_id' => $firstPayment->student_id,
            'program' => 'Manual Entry',
            'year' => 'N/A',
            'section' => 'N/A',
            'total_balance' => (float) $totalBalance,
            'total_paid' => (float) $totalPaid,
            'total_unpaid' => (float) $totalUnpaid,
            'paid_requirements' => $paidRequirements->toArray(),
            'unpaid_requirements' => $unpaidRequirements->toArray(),
            'payment_history' => $paymentHistory->toArray(),
            'paid_requirements_count' => $paidRequirements->count(),
            'unpaid_requirements_count' => $unpaidRequirements->count(),
        ];
    }

    /**
     * Get detailed user payment profile
     */
    public function show($id)
    {
        try {
            $allRequirements = Requirement::all();
            
            if (str_starts_with($id, 'manual_')) {
                $groupId = str_replace('manual_', '', $id);
                $payments = Payment::where(function($query) use ($groupId) {
                    if (is_numeric($groupId)) {
                        $query->where('id', $groupId);
                    } else {
                        $query->where('student_id', $groupId);
                    }
                })
                ->whereNull('user_id')
                ->with('requirement')
                ->get();
                
                $userData = $this->formatManualPaymentData($payments, $groupId, $allRequirements);
            } else {
                $user = User::with(['payments.requirement'])
                    ->findOrFail($id);
                $userData = $this->formatUserPaymentData($user, $allRequirements);
            }

            return response()->json($this->ensureArrayStructure($userData));
            
        } catch (\Exception $e) {
            Log::error('Error in UserProfileController@show: ' . $e->getMessage());
            return response()->json(['error' => 'User profile not found'], 404);
        }
    }
}