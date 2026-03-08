<?php

namespace App\Http\Controllers;

use App\Models\FileRecord;
use App\Models\FileMovement;
use App\Models\MovementAlert;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OutgoingMonitorController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display all documents created by the logged-in user that are still active.
     */
    public function index()
    {
        $userId = Auth::id();

        $files = FileRecord::with(['status', 'currentDepartment', 'currentOwner', 'originatingDepartment'])
            ->where('created_by', $userId)
            ->whereHas('status', function ($q) {
                $q->where('is_terminal', false);
            })
            ->orderBy('priority_level', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        // Eager-load the latest movement for each file
        $fileIds = $files->pluck('id');
        $latestMovements = FileMovement::whereIn('file_id', $fileIds)
            ->with(['toUser', 'toDepartment', 'fromUser', 'fromDepartment'])
            ->orderBy('dispatched_at', 'desc')
            ->get()
            ->groupBy('file_id')
            ->map(fn($group) => $group->first());

        // Load last alert timestamps for rate limit display
        $lastAlerts = MovementAlert::whereIn('file_id', $fileIds)
            ->where('alerted_by', $userId)
            ->orderBy('alerted_at', 'desc')
            ->get()
            ->groupBy('file_id')
            ->map(fn($group) => $group->first());

        return view('queues.outgoing-monitor', compact('files', 'latestMovements', 'lastAlerts'));
    }

    /**
     * Send an alert reminder to the current holder of a document.
     */
    public function sendAlert(Request $request, FileRecord $file)
    {
        $userId = Auth::id();

        // 1. Verify originator
        if ($file->created_by !== $userId) {
            return back()->withErrors(['error' => 'You are not the originator of this document.']);
        }

        // 2. Get latest movement
        $latestMovement = FileMovement::where('file_id', $file->id)
            ->orderBy('dispatched_at', 'desc')
            ->first();

        if (!$latestMovement || $latestMovement->movement_closed) {
            return back()->withErrors(['error' => 'No active movement found for this document.']);
        }

        // 3. Check elapsed time >= 72 hours
        $referenceTime = $latestMovement->received_at ?? $latestMovement->dispatched_at;
        $elapsedHours = Carbon::parse($referenceTime)->diffInHours(now());

        if ($elapsedHours < 72) {
            return back()->withErrors(['error' => 'Alert can only be sent after 72 hours of inactivity. Currently: ' . $elapsedHours . 'h elapsed.']);
        }

        // 4. Check rate limit — one alert per 24 hours per document
        $lastAlert = MovementAlert::where('file_id', $file->id)
            ->where('alerted_by', $userId)
            ->where('alerted_at', '>=', now()->subHours(24))
            ->exists();

        if ($lastAlert) {
            return back()->withErrors(['error' => 'You can only send one alert per document every 24 hours.']);
        }

        // 5. Record the alert
        MovementAlert::create([
            'file_id' => $file->id,
            'movement_id' => $latestMovement->id,
            'alerted_by' => $userId,
            'alerted_at' => now(),
        ]);

        // 6. Send notification(s) — clearly marked as MANUAL reminder from originator
        $originatorName = Auth::user()->name;
        $elapsed = Carbon::parse($referenceTime)->diffForHumans(now(), true);
        $message = "📩 Personal Reminder from {$originatorName}: Document \"{$file->title}\" ({$file->file_reference_number}) has been awaiting action for {$elapsed}.\n\n{$originatorName} is personally requesting that you review and process this document.\n\nPlease take action or forward the document to the appropriate party.";

        if ($latestMovement->to_user_id) {
            // Direct — notify the specific user
            $this->notificationService->send(
                $latestMovement->to_user_id,
                'DOCUMENT_ALERT',
                'file_records',
                $file->id,
                $message,
                'HIGH'
            );
        } else {
            // Department inbox — notify all department users
            $deptUsers = User::where('department_id', $latestMovement->to_department_id)
                ->where('is_active', true)
                ->pluck('id');

            foreach ($deptUsers as $deptUserId) {
                $this->notificationService->send(
                    $deptUserId,
                    'DOCUMENT_ALERT',
                    'file_records',
                    $file->id,
                    $message,
                    'HIGH'
                );
            }
        }

        // 7. Audit log
        DB::table('audit_logs')->insert([
            'agency_id' => $file->agency_id,
            'action_type' => 'DOCUMENT_ALERT_SENT',
            'entity_type' => 'file_records',
            'entity_id' => $file->id,
            'new_values' => json_encode([
                'movement_id' => $latestMovement->id,
                'detail' => 'Reminder sent to current holder',
            ]),
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Alert reminder sent successfully.');
    }
}
