<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileRecord;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    /**
     * Display files initiated by the current user that are actively in transit.
     */
    public function outgoing()
    {
        $userId = Auth::id();
        
        // Outgoing: Files I own, but are IN_TRANSIT, or last movement where I am the sender and it's pending.
        $files = FileRecord::with(['status', 'currentDepartment', 'movements' => function($q) {
                $q->with(['fromUser', 'toUser', 'fromDepartment', 'toDepartment'])->latest('dispatched_at')->limit(1);
            }])
            ->whereHas('movements', function($q) use ($userId) {
                $q->where('from_user_id', $userId)
                  ->where('acknowledgment_status', 'PENDING');
            })
            ->latest()
            ->paginate(25);

        return view('queues.outgoing', compact('files'));
    }

    /**
     * Display files dispatched to the user pending their acceptance.
     */
    public function incoming()
    {
        $userId = Auth::id();
        $userDeptId = Auth::user()->department_id;

        // Incoming: Files directed to the current user, awaiting acknowledgment.
        $files = FileRecord::with(['status', 'currentDepartment', 'movements' => function($q) {
                $q->with(['fromUser', 'toUser', 'fromDepartment', 'toDepartment'])->latest('dispatched_at')->limit(1);
            }])
            ->whereHas('movements', function($q) use ($userId) {
                $q->where('to_user_id', $userId)
                  ->where('acknowledgment_status', 'PENDING');
            })
            ->latest()
            ->paginate(25);

        // Incoming Jackets: dispatched to this user or department, pending receipt
        $incomingJackets = \App\Models\FileJacketMovement::with(['jacket', 'fromDepartment', 'fromUser'])
            ->where('status', 'PENDING_RECEIPT')
            ->where(function ($q) use ($userId, $userDeptId) {
                $q->where('to_user_id', $userId)
                  ->orWhere(function ($q2) use ($userDeptId) {
                      $q2->whereNull('to_user_id')
                         ->where('to_department_id', $userDeptId);
                  });
            })
            ->orderBy('dispatched_at', 'desc')
            ->get();

        return view('queues.incoming', compact('files', 'incomingJackets'));
    }

    /**
     * Display files physically resting with the user (Acknowledged, not in transit).
     */
    public function pending()
    {
        $userId = Auth::id();

        // Pending: Files I own that are NOT in a terminal stat or in transit.
        $files = FileRecord::with(['status', 'currentDepartment'])
            ->where('current_owner_id', $userId)
            ->whereHas('status', function($q) {
                $q->where('is_terminal', false)
                  ->where('name', '!=', 'IN_TRANSIT');
            })
            ->latest()
            ->paginate(25);

        return view('queues.pending', compact('files'));
    }

    /**
     * Display documents dispatched to the user's department inbox (no specific recipient).
     */
    public function departmentInbox()
    {
        $user = Auth::user();

        $files = \App\Models\FileMovement::with(['file.status', 'fromUser', 'fromDepartment', 'toDepartment'])
            ->where('to_department_id', $user->department_id)
            ->where('acknowledgment_status', 'PENDING')
            ->whereNull('to_user_id')
            ->orderBy('dispatched_at', 'asc')
            ->paginate(25);

        return view('queues.department-inbox', compact('files'));
    }
}
