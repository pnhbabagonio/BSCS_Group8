<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use Inertia\Inertia;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requirements = Requirement::latest()->get()->map(function ($requirement) {
            return [
                'id' => $requirement->id,
                'title' => $requirement->title,
                'description' => $requirement->description,
                'amount' => (float) $requirement->amount,
                'deadline' => $requirement->deadline->format('Y-m-d'),
                'total_users' => $requirement->total_users,
                'paid' => $requirement->paid,
                'unpaid' => $requirement->unpaid,
                'status' => $requirement->status,
                'created_at' => $requirement->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $requirement->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return JSON for API calls from the Requirements component
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['requirements' => $requirements]);
        }

        // For direct page access, return Inertia response
        return Inertia::render('PaymentManagement', [
            'requirements' => $requirements
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'deadline' => 'required|date|after:today',
            'total_users' => 'required|integer|min:1',
        ]);

        // Calculate initial values
        $validated['paid'] = 0;
        $validated['unpaid'] = $validated['total_users'];

        Requirement::create($validated);

        // Check if this is an Inertia request
        if ($request->header('X-Inertia')) {
            return redirect()->route('requirements.index')->with('success', 'Requirement created successfully.');
        }

        // For API calls, return JSON
        return response()->json(['success' => 'Requirement created successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'deadline' => 'required|date',
            'total_users' => 'required|integer|min:1',
        ]);

        // Recalculate unpaid based on current paid count
        $validated['unpaid'] = $validated['total_users'] - $requirement->paid;

        $requirement->update($validated);

        // Check if this is an Inertia request
        if ($request->header('X-Inertia')) {
            return redirect()->route('requirements.index')->with('success', 'Requirement updated successfully.');
        }

        // For API calls, return JSON
        return response()->json(['success' => 'Requirement updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requirement $requirement)
    {
        $requirement->delete();

        // Check if this is an Inertia request
        if (request()->header('X-Inertia')) {
            return redirect()->route('requirements.index')->with('success', 'Requirement deleted successfully.');
        }

        // For API calls, return JSON
        return response()->json(['success' => 'Requirement deleted successfully.']);
    }

    /**
     * Get users for a specific requirement (paid/unpaid)
     */
    public function getUsers(Requirement $requirement)
    {
        $paidUsers = $requirement->paidUsers()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $user->student_id,
                'program' => $user->program,
                'paid_at' => $user->pivot->paid_at->format('Y-m-d H:i:s'),
                'amount_paid' => (float) $user->pivot->amount_paid,
            ];
        });

        $unpaidUsers = $requirement->unpaidUsers()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $user->student_id,
                'program' => $user->program,
            ];
        });

        return response()->json([
            'paid_users' => $paidUsers,
            'unpaid_users' => $unpaidUsers,
        ]);
    }

    /**
     * Record a payment for a user
     */
    public function recordPayment(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount_paid' => 'required|numeric|min:0',
            'paid_at' => 'required|date',
        ]);

        // Attach or update payment record
        $requirement->users()->syncWithoutDetaching([
            $validated['user_id'] => [
                'amount_paid' => $validated['amount_paid'],
                'paid_at' => $validated['paid_at'],
                'status' => 'paid',
            ]
        ]);

        // Update paid count
        $requirement->update([
            'paid' => $requirement->calculatePaidCount(),
            'unpaid' => $requirement->total_users - $requirement->calculatePaidCount(),
        ]);

        return response()->json(['success' => 'Payment recorded successfully.']);
    }
}