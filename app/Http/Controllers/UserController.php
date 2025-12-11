<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Attendee;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select('id','name','email','student_id','program','year','role','status','last_login')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id ?? '',
                    'program' => $user->program ?? '',
                    'year' => $user->year ?? '',
                    'role' => $user->role,
                    'status' => $user->status,
                    'last_login' => $user->last_login ? $user->last_login->format('Y-m-d') : 'Never',
                ];
            });

        return Inertia::render('UserManagement', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','unique:users,email'],
            'studentId'  => ['nullable','string','max:50','unique:users,student_id'],
            'program'    => ['required','string','max:255'],
            'year'       => ['required','string','max:50'],
            'role'       => ['required','in:Member,Officer,Admin'], // Added Admin here
            'status'     => ['required','in:active,inactive'],
            'lastLogin'  => ['nullable','date'],
        ]);

        User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make('password123'), // Default password
            'student_id'  => $validated['studentId'] ?? null,
            'program'     => $validated['program'] ?? null,
            'year'        => $validated['year'] ?? null,
            'role'        => $validated['role'],
            'status'      => $validated['status'],
            'last_login'  => $validated['lastLogin'] ? now()->parse($validated['lastLogin']) : now(),
        ]);

        return redirect()->back()->with('success', 'User added successfully!');
    }

    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'student_id' => $user->student_id ?? '',
            'program' => $user->program ?? '',
            'year' => $user->year ?? '',
            'role' => $user->role,
            'status' => $user->status,
            'last_login' => $user->last_login ? $user->last_login->format('Y-m-d') : 'Never',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email', Rule::unique('users')->ignore($user->id)],
            'studentId'  => ['nullable','string','max:50', Rule::unique('users', 'student_id')->ignore($user->id)],
            'program'    => ['required','string','max:255'],
            'year'       => ['required','string','max:50'],
            'role'       => ['required','in:Member,Officer,Admin'], // Added Admin here too
            'status'     => ['required','in:active,inactive'],
            'lastLogin'  => ['nullable','date'],
        ]);

        $user->update([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'student_id'  => $validated['studentId'] ?? null,
            'program'     => $validated['program'] ?? null,
            'year'        => $validated['year'] ?? null,
            'role'        => $validated['role'],
            'status'      => $validated['status'],
            'last_login'  => $validated['lastLogin'] ? now()->parse($validated['lastLogin']) : $user->last_login,
        ]);

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully!');
    }

    // Add this method to your UserController.php

    public function getUsersForRegistration(Request $request)
    {
        // Get the event_id from the request if available
        $eventId = $request->query('event_id');
        
        $users = User::select('id', 'name', 'email', 'student_id', 'program', 'year', 'role', 'status')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($eventId) {
                // Get user's registration status for the specific event if event_id is provided
                $eventRegistration = null;
                if ($eventId) {
                    $eventRegistration = Attendee::where('user_id', $user->id)
                        ->where('event_id', $eventId)
                        ->first();
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                    'program' => $user->program,
                    'year' => $user->year,
                    'role' => $user->role,
                    'status' => $user->status,
                    // Add registration information for the event
                    'event_registration' => $eventRegistration ? [
                        'id' => $eventRegistration->id,
                        'attendance_status' => $eventRegistration->attendance_status,
                        'registered_at' => $eventRegistration->registered_at->format('Y-m-d H:i'),
                    ] : null,
                    // Add count of all event registrations (optional, for context)
                    'total_registrations' => $user->attendees()->count(),
                ];
            });

        return response()->json([
            'users' => $users
        ]);
    }
    
}