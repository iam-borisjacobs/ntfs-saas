<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $userId = Auth::id();
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $userId = Auth::id();
        if ($notification->user_id === $userId) {
            $this->notificationService->markAsRead($userId, $notification->id);
        }
        
        return back();
    }

    public function markAllAsRead()
    {
        $userId = Auth::id();
        $this->notificationService->markAsRead($userId);
        
        return back()->with('status', 'All notifications marked as read.');
    }
}
