<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCustodyIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nama:check-custody-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans files against their latest accepted movement to identify chain-of-custody discrepancies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning File tracking ledger for custody anomalies...");
        $anomalies = 0;

        // Note: In production with millions of files, chunking would be used here.
        \App\Models\FileRecord::chunk(500, function ($files) use (&$anomalies) {
            foreach ($files as $file) {
                // Find the absolute latest accepted movement for this file
                $latestAcceptedMovement = \App\Models\FileMovement::where('file_id', $file->id)
                    ->where('acknowledgment_status', 'ACCEPTED')
                    ->orderBy('received_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestAcceptedMovement) {
                    if ($file->current_owner_id !== $latestAcceptedMovement->to_user_id) {
                        \Illuminate\Support\Facades\Log::critical("SECURITY ALERT: Chain-of-Custody Violation Detected", [
                            'file_id' => $file->id,
                            'current_owner_id' => $file->current_owner_id,
                            'expected_owner_id' => $latestAcceptedMovement->to_user_id,
                            'movement_id' => $latestAcceptedMovement->id,
                        ]);
                        $this->error("Anomaly Detected: File #{$file->id} custody mismatch!");
                        $anomalies++;
                    }
                }
            }
        });

        if ($anomalies === 0) {
            $this->info("Scan Complete: Ledger integrity verified 100%. State matches accepted movements.");
        } else {
            $this->error("Scan Complete: {$anomalies} anomalies detected. See logs for details.");
        }
    }
}
