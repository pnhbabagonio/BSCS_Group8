<?php
// app/Http/Controllers/EventController.php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Remove the constructor or fix middleware usage
    // public function __construct()
    // {
    //     $this->middleware('auth')->except(['index', 'data', 'show']);
    // }

    public function index(Request $request): Response
    {
        return Inertia::render('Events', [
            'filters' => $request->only(['status', 'search']),
            'stats' => $this->getStats(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $status = $request->get('status', 'All');
        $search = $request->get('search', '');
        $user = $request->user();

        $events = Event::with(['user', 'registrations.user'])
            ->when($status !== 'All', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('venue', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12);

        $formattedEvents = $events->through(function ($event) use ($user) {
            return $this->formatEvent($event, $user);
        });

        return response()->json([
            'events' => $formattedEvents,
            'stats' => $this->getStats(),
        ]);
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $event->load(['user', 'registrations.user']);

        return response()->json([
            'event' => $this->formatEvent($event, $request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:today',
            'time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'status' => 'required|in:Upcoming,Ongoing,Completed',
            'max_capacity' => 'required|integer|min:1',
            'organizer' => 'required|string|max:255',
            'image_url' => 'nullable|url|max:500',
            'registration_deadline' => 'nullable|date|after:today',
        ]);

        try {
            $event = Event::create([
                ...$validated,
                'user_id' => $request->user()->id,
                'registered' => 0,
            ]);

            return redirect()->route('events.index')
                ->with('success', 'Event created successfully')
                ->with('created_event_id', $event->id);
        } catch (\Exception $e) {
            Log::error('Event creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create event');
        }
    }

    public function update(Request $request, Event $event)
    {
        if (!Gate::allows('update-event', $event)) {
            abort(403, 'Unauthorized to update this event');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'status' => 'required|in:Upcoming,Ongoing,Completed',
            'max_capacity' => 'required|integer|min:1',
            'organizer' => 'required|string|max:255',
            'image_url' => 'nullable|url|max:500',
            'registration_deadline' => 'nullable|date',
        ]);

        try {
            $event->update($validated);
            return back()->with('success', 'Event updated successfully');
        } catch (\Exception $e) {
            Log::error('Event update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update event');
        }
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        // Manual authorization
        if ($event->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized to delete this event');
        }

        try {
            $event->registrations()->delete();
            $event->delete();

            return redirect()->route('events.index')
                ->with('success', 'Event deleted successfully');
        } catch (\Exception $e) {
            Log::error('Event deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete event');
        }
    }

    public function myEvents(Request $request): JsonResponse
    {
        $events = Event::with(['user', 'registrations'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $formattedEvents = $events->through(function ($event) use ($request) {
            return $this->formatEvent($event, $request->user());
        });

        return response()->json([
            'events' => $formattedEvents,
        ]);
    }

    public function registeredEvents(Request $request): JsonResponse
    {
        $eventIds = Registration::where('user_id', $request->user()->id)
            ->whereIn('status', ['registered', 'waitlisted'])
            ->pluck('event_id');

        $events = Event::with(['user', 'registrations'])
            ->whereIn('id', $eventIds)
            ->latest()
            ->paginate(10);

        $formattedEvents = $events->through(function ($event) use ($request) {
            return $this->formatEvent($event, $request->user());
        });

        return response()->json([
            'events' => $formattedEvents,
        ]);
    }

    private function getStats(): array
    {
        $user = Auth::user(); // Use Auth facade instead of auth() helper

        $baseStats = [
            'total_events' => Event::count(),
            'upcoming_events' => Event::where('status', 'Upcoming')->count(),
            'ongoing_events' => Event::where('status', 'Ongoing')->count(),
            'completed_events' => Event::where('status', 'Completed')->count(),
            'total_registrations' => Registration::whereIn('status', ['registered', 'waitlisted'])->count(),
            'average_attendance' => round(Event::where('status', 'Completed')->avg('registered') ?? 0, 1),
        ];

        // Add user-specific stats if authenticated
        if ($user) {
            $baseStats['my_events'] = Event::where('user_id', $user->id)->count();
            $baseStats['my_registrations'] = Registration::where('user_id', $user->id)
                ->whereIn('status', ['registered', 'waitlisted'])
                ->count();
            $baseStats['my_attended_events'] = Registration::where('user_id', $user->id)
                ->where('status', 'attended')
                ->count();
        }

        return $baseStats;
    }

    private function formatEvent(Event $event, $user = null): array
    {
        $userRegistration = $user ? $event->registrations
            ->where('user_id', $user->id)
            ->first() : null;

        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'date' => $event->date->format('Y-m-d'),
            'formatted_date' => $event->date->format('M j, Y'),
            'time' => date('g:i A', strtotime($event->time)),
            'venue' => $event->venue,
            'address' => $event->address,
            'status' => $event->status,
            'registered_count' => $event->registrations()->where('status', 'registered')->count(),
            'waitlisted_count' => $event->registrations()->where('status', 'waitlisted')->count(),
            'max_capacity' => $event->max_capacity,
            'organizer' => $event->organizer,
            'image_url' => $event->image_url,
            'registration_deadline' => $event->registration_deadline?->format('Y-m-d\TH:i'),
            'user_id' => $event->user_id,
            'created_by' => $event->user->name,

            // Registration status for current user
            'has_available_spots' => $event->registrations()->where('status', 'registered')->count() < $event->max_capacity,
            'is_user_registered' => $userRegistration && $userRegistration->status === 'registered',
            'is_user_waitlisted' => $userRegistration && $userRegistration->status === 'waitlisted',
            'user_registration_status' => $userRegistration?->status,
            'user_registration_id' => $userRegistration?->id,
            'can_register' => $this->canRegister($event, $user),
            'can_cancel' => $userRegistration ? $this->canCancel($userRegistration) : false,
            'waitlist_position' => $userRegistration ? $this->getWaitlistPosition($userRegistration) : null,

            // Timestamps
            'created_at' => $event->created_at->toISOString(),
            'updated_at' => $event->updated_at->toISOString(),

            // Admin/Owner info
            'is_owner' => $user ? $event->user_id === $user->id : false,
            'can_manage' => $user ? $event->user_id === $user->id : false,
        ];
    }

    /**
     * Check if user can register for event
     */
    private function canRegister(Event $event, $user): bool
    {
        if (!$user) return false;

        // Check if already registered
        $existingRegistration = $event->registrations()
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
}
