<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Define the job's retry attempts and backoff
     */
    public $tries = 3;

    public $backoff = [30, 60, 120];

    protected $notificationId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification = Notification::find($this->notificationId);

        if (! $notification) {
            return;
        }

        // Email delivery is simulated as secondary channel per requirements.
        // Fails silently to the user, logs internally.
        try {
            if (in_array($notification->severity, ['HIGH', 'CRITICAL'])) {
                // E.g., Mail::to($notification->user->email)->send(new AlertMail($notification));
                Log::channel('daily')->info("SEC-ALERT: Async Secondary Channel (EMAIL) dispatch completed for Notification [{$notification->id}] to User [{$notification->user_id}].");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send secondary email for Notification [{$notification->id}]: ".$e->getMessage());
            // Does not re-throw, so custody system is not blocked
        }
    }
}
