<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Get all events that members can view
     */
    public function index()
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
     * Get specific event details
     */
    public function show($id)
    {
        $event = Event::active()->findOrFail($id, [
            'id', 'title', 'description', 'date', 'time', 'location', 
            'capacity', 'category', 'status'
        ]);

        return response()->json([
            'event' => $event,
            'registered_count' => $event->registered,
            'is_full' => $event->is_full
        ]);
    }

    /**
     * Register authenticated member for an event
     */
    public function register(Request $request, $eventId)
    {
        $user = Auth::user();
        $event = Event::active()->findOrFail($eventId);

        // Check if event is full
        if ($event->is_full) {
            return response()->json([
                'message' => 'Event is full. Cannot register.'
            ], 400);
        }

        // Check if already registered
        $existingRegistration = Attendee::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->where('attendance_status', '!=', 'cancelled')
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'message' => 'You are already registered for this event.'
            ], 400);
        }

        // Create registration
        $attendee = Attendee::create([
            'event_id' => $eventId,
            'user_id' => $user->id,
            'attendance_status' => 'registered'
        ]);

        return response()->json([
            'message' => 'Successfully registered for the event.',
            'data' => [
                'attendee' => $attendee,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->date,
                    'time' => $event->time,
                    'location' => $event->location
                ]
            ]
        ], 201);
    }

    /**
     * Cancel registration for an event
     */
    public function cancelRegistration($eventId)
    {
        $user = Auth::user();

        $attendee = Attendee::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->where('attendance_status', '!=', 'cancelled')
            ->first();

        if (!$attendee) {
            return response()->json([
                'message' => 'You are not registered for this event.'
            ], 404);
        }

        // Update status to cancelled
        $attendee->update([
            'attendance_status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Registration cancelled successfully.',
            'data' => [
                'attendee_id' => $attendee->id,
                'event_id' => $eventId
            ]
        ]);
    }

    /**
     * Check if user is registered for an event
     */
    public function checkRegistration($eventId)
    {
        $user = Auth::user();

        $attendee = Attendee::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->where('attendance_status', '!=', 'cancelled')
            ->first();

        return response()->json([
            'is_registered' => !is_null($attendee),
            'registration_status' => $attendee ? $attendee->attendance_status : null,
            'registered_at' => $attendee ? $attendee->created_at : null
        ]);
    }
}