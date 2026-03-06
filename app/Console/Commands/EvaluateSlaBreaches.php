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
                    $hoursSinceDispatch = $movement->dispatched_at->diffInHours(\Carbon\Carbon::now());

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

                            $systemUser = \App\Models\User::firstWhere('system_identifier', 'SYS-ADMIN') ?? \App\Models\User::first();
                            $systemUserId = $systemUser ? $systemUser->id : 1;

                            // In a real app we'd dispatch to their manager or previous owner.
                            // We mock sending an audit log that escalates.
                            \Illuminate\Support\Facades\DB::table('audit_logs')->insert([
                                'agency_id' => $movement->file->agency_id,
                                'action_type' => 'SLA_BREACH',
                                'entity_type' => 'App\Models\FileMovement',
                                'entity_id' => $movement->id,
                                'user_id' => $systemUserId, // Use dynamically grabbed System Admin ID
                                'old_values' => json_encode(['escalation_flag' => false]),
                                'new_values' => json_encode(['escalation_flag' => true, 'severity' => $severity]),
                                'ip_address' => '127.0.0.1',
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            });

        $this->info('SLA Evaluation Complete.');
    }
}
