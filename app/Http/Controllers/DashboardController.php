<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileRecord;
use App\Models\FileMovement;
use Illuminate\Support\Facades\DB;
use App\Services\FileStateService;

class DashboardController extends Controller
{
    public function index(Request $request, FileStateService $stateService)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $clearance = $user->clearance_level;
        $userId = $user->id;

        $inTransitStatusId = $stateService->getStatusIdByName('IN_TRANSIT');
        
        $incomingCount = FileMovement::where('to_user_id', $userId)
            ->where('acknowledgment_status', 'PENDING')
            ->count();
            
        $outgoingCount = FileMovement::where('from_user_id', $userId)
            ->where('acknowledgment_status', 'PENDING')
            ->count();
            
        $pendingCount = FileRecord::where('current_owner_id', $userId)
            ->whereNotIn('status_id', [$inTransitStatusId])
            ->whereHas('status', function($q) {
                $q->where('is_terminal', false);
            })->count();

        $unreadNotificationsCount = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        $deptInboxCount = FileMovement::where('to_department_id', $user->department_id)
            ->where('acknowledgment_status', 'PENDING')
            ->whereNull('to_user_id')
            ->count();

        $actionRequiredIds = FileMovement::where('to_user_id', $userId)
            ->where('acknowledgment_status', 'PENDING')
            ->pluck('file_id')
            ->concat(
                FileRecord::where('current_owner_id', $userId)
                    ->whereNotIn('status_id', [$inTransitStatusId])
                    ->whereHas('status', function($q) {
                        $q->where('is_terminal', false);
                    })->pluck('id')
            )->unique();

        $activeFiles = FileRecord::with(['status'])
            ->whereIn('id', $actionRequiredIds)
            ->where('confidentiality_level', '<=', $clearance)
            ->orderBy('priority_level', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $escalatedMovements = collect();

        // ── General Pending: preview of top 5 files on user's desk ──
        $pendingFiles = FileRecord::with(['status'])
            ->where('current_owner_id', $userId)
            ->whereNotIn('status_id', [$inTransitStatusId])
            ->whereHas('status', function($q) {
                $q->where('is_terminal', false);
            })
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // ── General Notification: overdue movements (dispatched > 48h, not received) ──
        $overdueMovements = FileMovement::with(['file', 'fromUser'])
            ->where('to_user_id', $userId)
            ->where('acknowledgment_status', 'PENDING')
            ->where('dispatched_at', '<', now()->subHours(48))
            ->orderBy('dispatched_at', 'asc')
            ->limit(5)
            ->get();

        // ── Recent unread notifications for this user ──
        $recentNotifications = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $cache = new \App\Services\CacheService();
        $departments = $cache->getDepartments();
        $statuses = $cache->getStatuses();

        // Optional Phase 12 Digital Document metric
        $documentCount = config('digital_module.enabled', true) 
            ? \App\Models\Document::count() 
            : null;

        return view('dashboard', [
            'metrics' => [
                'outgoing' => $outgoingCount,
                'incoming' => $incomingCount,
                'pending' => $pendingCount,
                'notifications' => $unreadNotificationsCount,
                'deptInbox' => $deptInboxCount,
                'documents' => $documentCount,
            ],
            'activeFiles' => $activeFiles,
            'escalatedMovements' => $escalatedMovements,
            'pendingFiles' => $pendingFiles,
            'overdueMovements' => $overdueMovements,
            'recentNotifications' => $recentNotifications,
            'departments' => $departments,
            'statuses' => $statuses
        ]);
    }
}
