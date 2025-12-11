<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Get member's own profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $user->student_id,
                'program' => $user->program,
                'year' => $user->year,
                'role' => $user->role,
                'status' => $user->status,
                'last_login' => $user->last_login,
            ]
        ]);
    }

    /**
     * Get all events (member can view)
     */
    public function events(Request $request)
    {
        $events = Event::active()
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get(['id', 'title', 'description', 'date', 'time', 'location', 'category', 'status']);

        return response()->json([
            'events' => $events
        ]);
    }

    /**
     * Get member's payments
     */
    public function payments(Request $request)
    {
        $user = $request->user();
        
        $payments = Payment::where('user_id', $user->id)
            ->orWhere(function($query) use ($user) {
                $query->whereNull('user_id')
                      ->where('student_id', $user->student_id);
            })
            ->with(['requirement' => function($q) {
                $q->select('id', 'title', 'amount');
            }])
            ->orderBy('paid_at', 'desc')
            ->get();

        return response()->json([
            'payments' => $payments
        ]);
    }

    /**
     * Get member's requirements (both paid and unpaid)
     */
    public function requirements(Request $request)
    {
        $user = $request->user();
        
        // Get all requirements
        $allRequirements = Requirement::all(['id', 'title', 'description', 'amount', 'deadline']);
        
        // Get user's paid requirement IDs
        $paidIds = $user->paidRequirements()->pluck('requirement_id')->toArray();
        
        // Categorize requirements
        $requirements = $allRequirements->map(function($requirement) use ($paidIds) {
            $requirementArray = $requirement->toArray();
            $requirementArray['status'] = in_array($requirement->id, $paidIds) ? 'paid' : 'unpaid';
            return $requirementArray;
        });

        return response()->json([
            'requirements' => $requirements
        ]);
    }

    /**
     * Get member dashboard summary
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Count upcoming events
        $upcomingEvents = Event::upcoming()->count();
        
        // Count pending payments
        $pendingPayments = $user->unpaidRequirements()->count();
        
        // Get recent payment
        $recentPayment = Payment::where('user_id', $user->id)
            ->orWhere(function($query) use ($user) {
                $query->whereNull('user_id')
                      ->where('student_id', $user->student_id);
            })
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->first();

        return response()->json([
            'summary' => [
                'upcoming_events' => $upcomingEvents,
                'pending_payments' => $pendingPayments,
                'member_since' => $user->created_at->format('F Y'),
                'recent_payment' => $recentPayment ? [
                    'amount' => $recentPayment->amount_paid,
                    'date' => $recentPayment->paid_at->format('M d, Y'),
                    'requirement' => $recentPayment->requirement->title ?? 'N/A'
                ] : null
            ]
        ]);
    }

    /**
 * Get events that the authenticated member has joined
 */
    public function joinedEvents()
    {
        $user = Auth::user();
        
        $joinedEvents = $user->attendees()
            ->with('event')
            ->where('attendance_status', '!=', 'cancelled')
            ->get()
            ->map(function ($attendee) {
                return [
                    'id' => $attendee->event->id,
                    'title' => $attendee->event->title,
                    'description' => $attendee->event->description,
                    'date' => $attendee->event->date,
                    'time' => $attendee->event->time,
                    'location' => $attendee->event->location,
                    'category' => $attendee->event->category,
                    'status' => $attendee->event->status,
                    'attendance_status' => $attendee->attendance_status,
                    'registered_at' => $attendee->created_at,
                    'event_status' => $attendee->event->status
                ];
            });

        return response()->json([
            'joined_events' => $joinedEvents,
            'total_count' => $joinedEvents->count()
        ]);
    }
}