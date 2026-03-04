<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EvaluateSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nama:evaluate-sla-breaches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluates pending movements against SLA limits and triggers required escalations.';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\NotificationService $notificationService)
    {
        $this->info('Starting SLA Evaluation Cycle...');

        // Process pending movements in batches to prevent memory exhaustion
        \App\Models\FileMovement::with(['file.status', 'toUser', 'fromUser'])
            ->where('acknowledgment_status', 'PENDING')
            ->where('escalation_flag', false) // Only escalate un-flagged movements initially
            ->chunkById(100, function ($movements) use ($notificationService) {
                foreach ($movements as $movement) {

                    // Skip if the file magically reached a terminal state while movement stayed pending
                    if ($movement->file->status->is_terminal) {
                        continue;
                    }

                    $hoursSinceDispatch = \Carbon\Carbon::now()->diffInHours($movement->dispatched_at);

                    // Thresholds (In production, this would query workflow_escalation_rules)
                    // Level 1: > 24 Hours
                    if ($hoursSinceDispatch >= 24) {

                        $severity = $hoursSinceDispatch >= 48 ? 'CRITICAL' : 'HIGH';

                        $movement->update(['escalation_flag' => true]);

                        $msgTarget = "URGENT ACTION REQUIRED: File {$movement->file->file_reference_number} assigned to you has breached SLA ({$hoursSinceDispatch}h pending).";

                        // Notify Target Custodian
                        $notificationService->send(
                            $movement->to_user_id,
                            'SLA_BREACH',
                            'App\Models\FileRecord',
                            $movement->file_id,
                            $msgTarget,
                            $severity
                        );

                        // If critical (> 48h), escalate back to Sender
                        if ($severity === 'CRITICAL') {
                            $msgSender = "ESCALATION ALERT: File {$movement->file->file_reference_number} dispatched to {$movement->toUser->name} is CRITICALLY overdue ({$hoursSinceDispatch}h).";

                            $notificationService->send(
                                $movement->from_user_id,
                                'ESCALATION_L1',
                                'App\Models\FileRecord',
                                $movement->file_id,
                                $msgSender,
                                'CRITICAL'
                            );
                        }
                    }
                }
            });

        $this->info('SLA Evaluation Complete.');
    }
}
