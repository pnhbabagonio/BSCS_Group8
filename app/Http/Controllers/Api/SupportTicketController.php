<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    /**
     * Store a newly created support ticket.
     */
    public function store(CreateSupportTicketRequest $request): JsonResponse
    {
        try {
            // Generate a unique reference number
            $referenceNumber = 'TICKET-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
            
            // Create the support ticket
            $supportTicket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $request->subject,
                'message' => $request->message,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => 'open', // Default status
                'attachments' => $request->attachments ?? [],
                // You can add: 'reference_number' => $referenceNumber if you add this field to your model
            ]);

            // You might want to send email notifications here
            // $this->sendTicketNotification($supportTicket);

            return response()->json([
                'success' => true,
                'message' => 'Support ticket created successfully!',
                'data' => [
                    'ticket' => $supportTicket,
                    'reference_number' => $referenceNumber,
                ],
                'instructions' => 'Our support team will review your ticket and get back to you soon.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create support ticket.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all support tickets for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $tickets->count(),
            'data' => [
                'tickets' => $tickets
            ]
        ]);
    }

    /**
     * Get a specific support ticket.
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        // Ensure the user can only view their own tickets
        if ($ticket->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this ticket.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'user' => $ticket->user->only(['id', 'name', 'email']) // Only show minimal user info
            ]
        ]);
    }

    /**
     * Helper method to send notification (optional).
     */
    private function sendTicketNotification(SupportTicket $ticket): void
    {
        // You can implement email notification here
        // Example: Mail::to($ticket->user->email)->send(new TicketCreated($ticket));
        
        // Or send notification to admin/support team
        // Example: Mail::to(config('app.support_email'))->send(new NewTicketNotification($ticket));
    }
}