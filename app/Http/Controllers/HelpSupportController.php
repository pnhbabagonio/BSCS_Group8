<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HelpSupportController extends Controller
{
    public function index()
    {
        $userTickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'message' => $ticket->message,
                    'category' => $ticket->category,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'attachments' => $ticket->attachments ?? [],
                    'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $ticket->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('HelpSupport', [
            'userTickets' => $userTickets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'attachments.*' => ['nullable', 'file', 'max:10240'], // 10MB max each
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('support_attachments', 'public');
                    $attachmentPaths[] = $path;
                }
            }
        }

        SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
            'attachments' => $attachmentPaths,
        ]);

        return redirect()->back()->with('status', 'Your support ticket has been submitted successfully!');
    }
}