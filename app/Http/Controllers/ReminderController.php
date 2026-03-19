<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReminderController extends Controller
{
    /**
     * Get reminders for a specific month and year.
     */
    public function index(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);

        $reminders = Reminder::where('user_id', Auth::id())
            ->whereYear('reminder_date', $year)
            ->whereMonth('reminder_date', $month)
            ->get();

        return response()->json($reminders);
    }

    /**
     * Store a new reminder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reminder_date' => 'required|date|after_or_equal:today|before_or_equal:+12 months',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $reminder = Reminder::create([
            'user_id' => Auth::id(),
            'reminder_date' => $validated['reminder_date'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed' => false,
        ]);

        return response()->json([
            'message' => 'Reminder created successfully',
            'reminder' => $reminder
        ], 201);
    }

    /**
     * Mark a reminder as completed or incomplete.
     */
    public function updateStatus(Request $request, Reminder $reminder)
    {
        // Ensure user owns reminder
        if ($reminder->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'is_completed' => 'required|boolean'
        ]);

        $reminder->update(['is_completed' => $validated['is_completed']]);

        return response()->json(['message' => 'Status updated', 'reminder' => $reminder]);
    }

    /**
     * Delete a reminder.
     */
    public function destroy(Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reminder->delete();

        return response()->json(['message' => 'Reminder deleted']);
    }
}
