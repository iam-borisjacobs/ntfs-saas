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
                $q->latest('dispatched_at')->limit(1);
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

        // Incoming: Files directed to the current user, awaiting acknowledgment.
        $files = FileRecord::with(['status', 'currentDepartment', 'movements' => function($q) {
                $q->latest('dispatched_at')->limit(1);
            }])
            ->whereHas('movements', function($q) use ($userId) {
                $q->where('to_user_id', $userId)
                  ->where('acknowledgment_status', 'PENDING');
            })
            ->latest()
            ->paginate(25);

        return view('queues.incoming', compact('files'));
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
}
