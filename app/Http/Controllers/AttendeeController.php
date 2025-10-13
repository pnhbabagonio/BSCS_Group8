<?php

namespace App\Http\Controllers;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\Attendee;
class AttendeeController extends Controller
{
    public function index()
    {
        $attendees = Attendee::with(['user', 'event'])
            ->orderBy('registered_at', 'desc')
            ->get()
            ->map(function ($attendee) {
                return [
                    'id' => $attendee->id,
                    'first_name' => $attendee->user->name,
                    'last_name' => '',
                    'email' => $attendee->user->email,
                    'status' => $attendee->attendance_status,
                    'checkin_type' => 'manual',
                    'checkin_time' => $attendee->registered_at->format('H:i'),
                    'date' => $attendee->registered_at->format('Y-m-d'),
                    'event_title' => $attendee->event->title,
                ];
            });

        return Inertia::render('Attendee', [
            'attendees' => $attendees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'attendance_status' => 'required|in:registered,attended,cancelled',
        ]);

        // Check if already registered
        $existingAttendee = Attendee::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingAttendee) {
            return redirect()->back()->with('error', 'User is already registered for this event.');
        }

        // Check event capacity
        $event = Event::find($validated['event_id']);
        $currentRegistered = $event->attendees()->where('attendance_status', '!=', 'cancelled')->count();
        
        if ($currentRegistered >= $event->capacity) {
            return redirect()->back()->with('error', 'Event is at full capacity.');
        }

        Attendee::create([
            'event_id' => $validated['event_id'],
            'user_id' => $validated['user_id'],
            'attendance_status' => $validated['attendance_status'],
            'registered_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Attendee registered successfully!');
    }

    public function update(Request $request, Attendee $attendee)
    {
        $validated = $request->validate([
            'attendance_status' => 'required|in:registered,attended,cancelled',
        ]);

        $attendee->update($validated);

        return response()->json(['message' => 'Attendee status updated successfully']);
    }

    public function destroy(Attendee $attendee)
    {
        $attendee->delete();

        return response()->json(['message' => 'Attendee removed successfully']);
    }
    /**
    * Export attendees (mock only)
    */
    public function export(): StreamedResponse
    {
        $attendees = Attendee::with(['user', 'event'])
            ->orderBy('registered_at', 'desc')
            ->get()
            ->map(function ($attendee) {
                return [
                    'id' => $attendee->id,
                    'first_name' => $attendee->user->name,
                    'middle_name' => '', // Adjust if you have this field
                    'last_name' => '',   // Adjust if you have this field
                    'email' => $attendee->user->email,
                    'status' => $attendee->attendance_status,
                    'checkin_type' => 'manual',
                    'checkin_time' => $attendee->registered_at->format('H:i'),
                    'date' => $attendee->registered_at->format('Y-m-d'),
                ];
            });

        $csv = fopen('php://temp', 'r+');

        // Header row
        fputcsv($csv, [
            'ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email',
            'Status',
            'Check-in Type',
            'Check-in Time',
            'Date'
        ]);

        // Data rows
        foreach ($attendees as $attendee) {
            fputcsv($csv, [
                $attendee['id'],
                $attendee['first_name'],
                $attendee['middle_name'] ?? '',
                $attendee['last_name'],
                $attendee['email'],
                ucfirst($attendee['status']),
                $attendee['checkin_type'] ?? '',
                $attendee['checkin_time'] ?? '',
                $attendee['date'],
            ]);
        }

        rewind($csv);

        return response()->streamDownload(function () use ($csv) {
            fpassthru($csv);
        }, 'attendees.csv', ['Content-Type' => 'text/csv']);
    }
}
