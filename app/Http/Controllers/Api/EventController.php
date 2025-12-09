<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;

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
}