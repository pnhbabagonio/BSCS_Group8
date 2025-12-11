<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Requirement;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'requirement']);

        // Search filter - search in both user names and manual names
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->get()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'user_id' => $payment->user_id,
                'requirement_id' => $payment->requirement_id,
                'amount_paid' => (float) $payment->amount_paid,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : null,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
                'first_name' => $payment->first_name,
                'middle_name' => $payment->middle_name,
                'last_name' => $payment->last_name,
                'student_id' => $payment->student_id,
                'user' => $payment->user ? [
                    'id' => $payment->user->id,
                    'first_name' => $payment->user->first_name ?? $this->extractFirstName($payment->user->name),
                    'middle_name' => $payment->user->middle_name ?? $this->extractMiddleName($payment->user->name),
                    'last_name' => $payment->user->last_name ?? $this->extractLastName($payment->user->name),
                    'student_id' => $payment->user->student_id,
                ] : null,
                'requirement' => [
                    'id' => $payment->requirement->id,
                    'title' => $payment->requirement->title,
                    'amount' => (float) $payment->requirement->amount,
                ],
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $payment->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Get requirements
        $requirements = Requirement::select('id', 'title', 'amount')->get()->map(function ($requirement) {
            return [
                'id' => $requirement->id,
                'title' => $requirement->title,
                'amount' => (float) $requirement->amount,
            ];
        });


        // Get users - handle both name formats
        $users = User::select('id', 'name', 'first_name', 'middle_name', 'last_name', 'student_id')
            ->where('status', 'active') // Only active users
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name ?? $this->extractFirstName($user->name),
                    'middle_name' => $user->middle_name ?? $this->extractMiddleName($user->name),
                    'last_name' => $user->last_name ?? $this->extractLastName($user->name),
                    'student_id' => $user->student_id,
                    'full_name' => $user->first_name && $user->last_name
                        ? trim("{$user->first_name} {$user->middle_name} {$user->last_name}")
                        : $user->name,
                ];
            });

        // Return JSON for API calls from the Records component
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json([
                'payments' => $payments,
                'requirements' => $requirements,
                'users' => $users,
                'filters' => [
                    'search' => $request->search,
                    'status' => $request->status ?? 'All'
                ]
            ]);
        }

        // For direct page access
        return Inertia::render('PaymentManagement', [
            'payments' => $payments,
            'requirements' => $requirements,
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status ?? 'All'
            ]
        ]);
    }

    /**
     * Helper methods to extract name parts from full name
     */
    private function extractFirstName($fullName)
    {
        $names = explode(' ', $fullName);
        return $names[0] ?? $fullName;
    }

    private function extractMiddleName($fullName)
    {
        $names = explode(' ', $fullName);
        return count($names) > 2 ? $names[1] : null;
    }

    private function extractLastName($fullName)
    {
        $names = explode(' ', $fullName);
        return count($names) > 1 ? end($names) : null;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'requirement_id' => 'required|exists:requirements,id',
            'amount_paid' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
            'status' => 'required|in:pending,paid,unpaid',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            // Manual user info - only validate if user_id is null
            'first_name' => 'required_if:user_id,null|string|max:255|nullable',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required_if:user_id,null|string|max:255|nullable',
            'student_id' => 'nullable|string|max:255',
        ]);

        // If status is paid and no paid_at date, set to current date
        if ($validated['status'] === 'paid' && empty($validated['paid_at'])) {
            $validated['paid_at'] = now();
        }

        // If user_id is provided, clear manual user info
        if (!empty($validated['user_id'])) {
            $validated['first_name'] = null;
            $validated['middle_name'] = null;
            $validated['last_name'] = null;
            $validated['student_id'] = null;
        }

        $payment = Payment::create($validated);

        // Update the requirement's paid/unpaid counts
        $this->updateRequirementCounts($validated['requirement_id']);

        // Return JSON response for API calls
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => 'Payment record created successfully.']);
        }

        return redirect()->route('records.index')->with('success', 'Payment record created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'requirement_id' => 'required|exists:requirements,id',
            'amount_paid' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
            'status' => 'required|in:pending,paid,unpaid',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            // Manual user info - only validate if user_id is null
            'first_name' => 'required_if:user_id,null|string|max:255|nullable',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required_if:user_id,null|string|max:255|nullable',
            'student_id' => 'nullable|string|max:255',
        ]);

        // If status changed to paid and no paid_at date, set to current date
        if ($validated['status'] === 'paid' && empty($validated['paid_at'])) {
            $validated['paid_at'] = now();
        }
        // If user_id is provided, clear manual user info
        if (!empty($validated['user_id'])) {
            $validated['first_name'] = null;
            $validated['middle_name'] = null;
            $validated['last_name'] = null;
            $validated['student_id'] = null;
        }

        $oldRequirementId = $payment->requirement_id;
        $payment->update($validated);

        // Update counts for both old and new requirements if requirement changed
        if ($oldRequirementId != $validated['requirement_id']) {
            $this->updateRequirementCounts($oldRequirementId);
        }
        $this->updateRequirementCounts($validated['requirement_id']);

        // Return JSON response for API calls
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => 'Payment record updated successfully.']);
        }

        return redirect()->route('records.index')->with('success', 'Payment record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $requirementId = $payment->requirement_id;
        $payment->delete();

        // Update the requirement's paid/unpaid counts
        $this->updateRequirementCounts($requirementId);

        // Return JSON response for API calls
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => 'Payment record deleted successfully.']);
        }

        return redirect()->route('records.index')->with('success', 'Payment record deleted successfully.');
    }

    /**
     * Delete multiple payment records (batch delete)
     */
    public function batchDestroy(Request $request)
    {
        $validated = $request->validate([
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['integer', 'exists:payments,id'],
        ]);

        $payments = Payment::whereIn('id', $validated['payment_ids'])->get();
        $requirementIds = $payments->pluck('requirement_id')->unique();

        // Delete all selected payments
        foreach ($payments as $payment) {
            $payment->delete();
        }

        // Update counts for all affected requirements
        foreach ($requirementIds as $requirementId) {
            $this->updateRequirementCounts($requirementId);
        }

        // Return JSON response for API calls
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => 'Payment records deleted successfully.']);
        }

        return redirect()->route('records.index')->with('success', 'Selected payment records deleted successfully.');
    }

    /**
     * Update paid and unpaid counts for a requirement
     */
    private function updateRequirementCounts($requirementId)
    {
        $requirement = Requirement::find($requirementId);
        if (!$requirement) {
            return;
        }

        // Count distinct users who have paid for this requirement
        $paidCount = Payment::where('requirement_id', $requirementId)
            ->where('status', 'paid')
            ->distinct('user_id')
            ->count('user_id');

        // Also count manual entries (where user_id is null but status is paid)
        $manualPaidCount = Payment::where('requirement_id', $requirementId)
            ->where('status', 'paid')
            ->whereNull('user_id')
            ->count();

        $totalPaid = $paidCount + $manualPaidCount;
        $unpaidCount = $requirement->total_users - $totalPaid;

        // Ensure counts don't go negative
        $unpaidCount = max(0, $unpaidCount);

        $requirement->update([
            'paid' => $totalPaid,
            'unpaid' => $unpaidCount,
        ]);
    }

    /**
     * Get payment statistics
     */
    public function getStats()
    {
        $totalPayments = Payment::count();
        $totalAmount = (float) Payment::sum('amount_paid');
        $paidCount = Payment::where('status', 'paid')->count();
        $pendingCount = Payment::where('status', 'pending')->count();
        $unpaidCount = Payment::where('status', 'unpaid')->count();

        return response()->json([
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'unpaid_count' => $unpaidCount,
        ]);
    }
}
