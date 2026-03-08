<?php

namespace App\Console\Commands;

use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\MovementAlert;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOverdueDocuments extends Command
{
    protected $signature = 'documents:check-overdue';

    protected $description = 'Send automatic system reminders for documents idle for 48+ business hours';

    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking for overdue documents...');

        // Get all active (non-terminal) file records
        $activeFiles = FileRecord::whereHas('status', function ($q) {
            $q->where('is_terminal', false);
        })->pluck('id');

        if ($activeFiles->isEmpty()) {
            $this->info('No active files found.');
            return self::SUCCESS;
        }

        // Get the latest movement for each active file
        $latestMovements = FileMovement::whereIn('file_id', $activeFiles)
            ->where('movement_closed', false)
            ->whereNotNull('received_at')
            ->orderBy('dispatched_at', 'desc')
            ->get()
            ->unique('file_id');

        $sentCount = 0;

        foreach ($latestMovements as $movement) {
            $referenceTime = $movement->received_at;

            // Calculate business hours (exclude weekends)
            $businessHours = $this->calculateBusinessHours($referenceTime, now());

            if ($businessHours < 48) {
                continue;
            }

            // Check if system already sent an auto-reminder for this movement in the last 24h
            $recentAutoAlert = MovementAlert::where('movement_id', $movement->id)
                ->whereNull('alerted_by') // NULL = system
                ->where('alerted_at', '>=', now()->subHours(24))
                ->exists();

            if ($recentAutoAlert) {
                continue;
            }

            // Load the file
            $file = FileRecord::find($movement->file_id);
            if (!$file) continue;

            $elapsed = Carbon::parse($referenceTime)->diffForHumans(now(), true);

            $message = "⏰ System Reminder: Document \"{$file->title}\" ({$file->file_reference_number}) has been idle for {$elapsed}.\n\nThis is an automated reminder. Please review and process the document, or forward it to the appropriate party.";

            // Send to the holder
            if ($movement->to_user_id) {
                $notificationService->send(
                    $movement->to_user_id,
                    'SYSTEM_OVERDUE_REMINDER',
                    'file_records',
                    $file->id,
                    $message,
                    'HIGH'
                );
            } else {
                // Department inbox — notify all department members
                $deptUsers = User::where('department_id', $movement->to_department_id)
                    ->where('is_active', true)
                    ->pluck('id');

                foreach ($deptUsers as $deptUserId) {
                    $notificationService->send(
                        $deptUserId,
                        'SYSTEM_OVERDUE_REMINDER',
                        'file_records',
                        $file->id,
                        $message,
                        'HIGH'
                    );
                }
            }

            // Record the auto-alert (alerted_by = null for system)
            MovementAlert::create([
                'file_id' => $file->id,
                'movement_id' => $movement->id,
                'alerted_by' => null,
                'alerted_at' => now(),
            ]);

            // Audit log
            DB::table('audit_logs')->insert([
                'agency_id' => $file->agency_id,
                'action_type' => 'SYSTEM_OVERDUE_REMINDER',
                'entity_type' => 'file_records',
                'entity_id' => $file->id,
                'new_values' => json_encode([
                    'movement_id' => $movement->id,
                    'business_hours_elapsed' => $businessHours,
                    'detail' => 'Automatic system reminder sent after 48 business hours of inactivity',
                ]),
                'user_id' => User::firstWhere('system_identifier', 'SYS-ADMIN')?->id ?? 1,
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
            ]);

            $sentCount++;
        }

        $this->info("Done. Sent {$sentCount} automatic reminder(s).");
        Log::info("CheckOverdueDocuments: Sent {$sentCount} auto-reminder(s).");

        return self::SUCCESS;
    }

    /**
     * Calculate business hours between two timestamps, excluding weekends (Sat/Sun).
     */
    private function calculateBusinessHours(Carbon $start, Carbon $end): int
    {
        $totalHours = 0;
        $current = $start->copy();

        // If start is on a weekend, fast-forward to Monday
        while ($current->isWeekend()) {
            $current->addDay()->startOfDay();
        }

        while ($current->lt($end)) {
            if ($current->isWeekend()) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Count the remaining hours in this business day or until end
            $endOfDay = $current->copy()->endOfDay();
            $segmentEnd = $end->lt($endOfDay) ? $end : $endOfDay;
            $totalHours += (int) $current->diffInHours($segmentEnd);

            $current = $endOfDay->addSecond();
        }

        return $totalHours;
    }
}
