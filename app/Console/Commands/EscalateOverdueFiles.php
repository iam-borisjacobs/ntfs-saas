<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use App\Models\FileMovement;
use App\Models\FileRecord;
use Carbon\Carbon;

class EscalateOverdueFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fts:escalate-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan file_movements and flag delayed processes according to DB workflow SLA rules.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Escalation Engine sweep...");

        // 1. Load active escalation rules
        $rules = DB::table('workflow_rules')->get()->keyBy('status_id');

        if ($rules->isEmpty()) {
            $this->warn("No workflow_rules defined in database. Skipping sweep.");
            return;
        }

        // 2. Scan for overdue pending dispatch transfers or delayed files
        // (Simplified to pending movement SLA enforcement for Phase 5)
        $inTransitRule = $rules->firstWhere('status_id', DB::table('statuses')->where('name', 'IN_TRANSIT')->value('id'));

        if (!$inTransitRule) {
            $this->warn("No IN_TRANSIT SLA defined.");
            return;
        }

        $threshold = Carbon::now()->subHours($inTransitRule->max_duration_hours);

        $overdueMovements = FileMovement::where('acknowledgment_status', 'PENDING')
            ->where('escalation_flag', false)
            ->where('created_at', '<', $threshold)
            ->get();

        if ($overdueMovements->isEmpty()) {
            $this->info("Zero overdue movements found under high severity constraints.");
            return;
        }

        // 3. Flag and Log inside Transactions
        foreach ($overdueMovements as $movement) {
            DB::transaction(function () use ($movement, $inTransitRule) {
                // Lock row
                $lockedMovement = FileMovement::where('id', $movement->id)->lockForUpdate()->first();
                if ($lockedMovement && !$lockedMovement->escalation_flag) {
                    $lockedMovement->escalation_flag = true;
                    $lockedMovement->save();

                    // Generate Audit Trace
                    DB::table('audit_logs')->insert([
                        'agency_id' => $lockedMovement->agency_id,
                        'action_type' => 'ESCALATE',
                        'entity_type' => 'file_movements',
                        'entity_id' => $lockedMovement->id,
                        'new_values' => json_encode([
                            'escalation_flag' => true, 
                            'escalated_to_role_id' => $inTransitRule->escalation_role_id,
                            'delay_hours' => $inTransitRule->max_duration_hours
                        ]),
                        'user_id' => null, // Script execution
                        'created_at' => now(),
                    ]);
                }
            });
        }

        $this->info("Escalation Engine sweep complete. Flagged: " . $overdueMovements->count());
    }
}
