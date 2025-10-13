<?php
// app/Http/Controllers/RegistrationController.php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate; // Add this import
use Illuminate\Auth\Access\AuthorizationException;

class RegistrationController extends Controller
{
    public function store(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        // Check if user can register for this event
        if (!$this->canRegister($event, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot register for this event. It may be full, past, or you are already registered.'
            ], 422);
        }

        try {
            return DB::transaction(function () use ($event, $user) {
                // Check for existing registration
                $existingRegistration = Registration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['registered', 'waitlisted'])
                    ->first();

                if ($existingRegistration) {
                    return response()->json([
                        'success' => false,
                        'message' => $existingRegistration->status === 'registered'
                            ? 'You are already registered for this event.'
                            : 'You are already on the waitlist for this event.'
                    ], 409);
                }

                // Determine status based on available spots
                $registeredCount = Registration::where('event_id', $event->id)
                    ->where('status', 'registered')
                    ->count();

                $status = $registeredCount < $event->max_capacity
                    ? 'registered'
                    : 'waitlisted';

                // Create registration
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => $status,
                    'registered_at' => now(),
                ]);

                $message = $status === 'registered'
                    ? 'Successfully registered for the event!'
                    : 'Event is full. You have been added to the waitlist.';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'registration' => [
                        'id' => $registration->id,
                        'status' => $registration->status,
                        'waitlist_position' => $this->getWaitlistPosition($registration),
                        'registered_at' => $registration->registered_at->toISOString(),
                    ]
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'waitlisted'])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered for this event.'
            ], 404);
        }

        if (!$this->canCancel($registration)) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation is no longer available for this event.'
            ], 422);
        }

        try {
            return DB::transaction(function () use ($registration, $event) {
                $wasRegistered = $registration->status === 'registered';
                $registration->update(['status' => 'cancelled', 'cancelled_at' => now()]);

                // Promote first waitlisted user if this was a registered spot
                if ($wasRegistered) {
                    $this->promoteFirstWaitlisted($event);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Registration cancelled successfully.',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Registration cancellation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed. Please try again.'
            ], 500);
        }
    }

    public function getRegistrants(Request $request, Event $event): JsonResponse
    {
        // Use manual authorization instead of Gate to avoid issues
        if ($event->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized to view registrants for this event.');
        }

        $registrations = Registration::with('user')
            ->where('event_id', $event->id)
            ->whereIn('status', ['registered', 'waitlisted'])
            ->orderByRaw("FIELD(status, 'registered', 'waitlisted'), registered_at")
            ->get();

        $registrants = $registrations->where('status', 'registered')
            ->map(fn($reg) => $this->formatRegistrantData($reg));

        $waitlisted = $registrations->where('status', 'waitlisted')
            ->map(fn($reg) => $this->formatRegistrantData($reg));

        return response()->json([
            'registrants' => $registrants,
            'waitlisted' => $waitlisted,
            'stats' => [
                'registered_count' => $registrants->count(),
                'waitlisted_count' => $waitlisted->count(),
                'max_capacity' => $event->max_capacity,
                'available_spots' => max(0, $event->max_capacity - $registrants->count()),
            ]
        ]);
    }

    public function removeRegistrant(Request $request, Event $event, Registration $registration): JsonResponse
    {
        // Use manual authorization
        if ($event->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized to remove registrants from this event.');
        }

        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration does not belong to this event.'
            ], 422);
        }

        try {
            return DB::transaction(function () use ($registration, $event) {
                $wasRegistered = $registration->status === 'registered';
                $registration->update(['status' => 'cancelled', 'cancelled_at' => now()]);

                // Promote first waitlisted user if this was a registered spot
                if ($wasRegistered) {
                    $this->promoteFirstWaitlisted($event);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Registrant removed successfully.'
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Remove registrant failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove registrant.'
            ], 500);
        }
    }

    public function updateAttendance(Request $request, Event $event, Registration $registration): JsonResponse
    {
        // Use manual authorization
        if ($event->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized to update attendance for this event.');
        }

        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration does not belong to this event.'
            ], 422);
        }

        $validated = $request->validate([
            'attended' => 'required|boolean',
        ]);

        try {
            if ($validated['attended']) {
                $registration->update(['attended' => true, 'status' => 'attended']);
                $message = 'Attendance marked successfully.';
            } else {
                $registration->update(['attended' => false]);
                $message = 'Attendance unmarked successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'attended' => $registration->attended,
            ]);
        } catch (\Exception $e) {
            Log::error('Update attendance failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance.'
            ], 500);
        }
    }

    public function promoteFromWaitlist(Request $request, Event $event, Registration $registration): JsonResponse
    {
        // Use manual authorization
        if ($event->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized to promote waitlisted users for this event.');
        }

        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration does not belong to this event.'
            ], 422);
        }

        if (!$this->canBePromoted($registration)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot promote this registration. Event may be full or registration is not waitlisted.'
            ], 422);
        }

        try {
            $registration->update(['status' => 'registered']);

            return response()->json([
                'success' => true,
                'message' => 'User promoted from waitlist successfully.',
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'user_name' => $registration->user->name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Promote from waitlist failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to promote user from waitlist.'
            ], 500);
        }
    }

    /**
     * Format registrant data for response
     */
    private function formatRegistrantData(Registration $registration): array
    {
        return [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'name' => $registration->user->name,
            'email' => $registration->user->email,
            'registered_at' => $registration->registered_at->format('M j, Y g:i A'),
            'program' => $registration->user->program ?? 'N/A',
            'student_id' => $registration->user->student_id ?? 'N/A',
            'status' => $registration->status,
            'attended' => $registration->attended ?? false,
            'waitlist_position' => $this->getWaitlistPosition($registration),
            'can_be_promoted' => $this->canBePromoted($registration),
        ];
    }

    /**
     * Check if user can register for event
     */
    private function canRegister(Event $event, $user): bool
    {
        if (!$user) return false;

        // Check if already registered
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'waitlisted'])
            ->exists();

        if ($existingRegistration) {
            return false;
        }

        // Check registration deadline
        if ($event->registration_deadline && $event->registration_deadline->isPast()) {
            return false;
        }

        // Check if event is in the future
        if ($event->date->isPast()) {
            return false;
        }

        // Check if event is active
        if ($event->status !== 'Upcoming') {
            return false;
        }

        return true;
    }

    /**
     * Check if registration can be cancelled
     */
    private function canCancel($registration): bool
    {
        if ($registration->status === 'cancelled') {
            return false;
        }

        // Allow cancellation up to 24 hours before event or if event is completed
        if ($registration->event->status === 'Completed') {
            return false;
        }

        return $registration->event->date->subHours(24)->isFuture();
    }

    /**
     * Check if registration can be promoted from waitlist
     */
    private function canBePromoted($registration): bool
    {
        if ($registration->status !== 'waitlisted') {
            return false;
        }

        $registeredCount = Registration::where('event_id', $registration->event_id)
            ->where('status', 'registered')
            ->count();

        return $registeredCount < $registration->event->max_capacity;
    }

    /**
     * Get waitlist position
     */
    private function getWaitlistPosition($registration): ?int
    {
        if ($registration->status !== 'waitlisted') {
            return null;
        }

        return Registration::where('event_id', $registration->event_id)
            ->where('status', 'waitlisted')
            ->where('registered_at', '<=', $registration->registered_at)
            ->count();
    }

    /**
     * Promote first waitlisted user
     */
    private function promoteFirstWaitlisted(Event $event): void
    {
        $firstWaitlisted = Registration::where('event_id', $event->id)
            ->where('status', 'waitlisted')
            ->orderBy('registered_at')
            ->first();

        if ($firstWaitlisted && $this->canBePromoted($firstWaitlisted)) {
            $firstWaitlisted->update(['status' => 'registered']);
        }
    }
}
