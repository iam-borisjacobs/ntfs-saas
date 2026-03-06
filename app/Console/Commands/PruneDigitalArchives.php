<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneDigitalArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'digital:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete or archive digital documents past their retention period.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!config('digital_module.enabled', true)) {
            $this->info('Digital module is disabled. Exiting.');
            return;
        }

        $retentionConfig = config('digital_module.retention', []);
        
        $this->info('Starting digital document retention pruning...');

        $prunedCount = 0;

        foreach ($retentionConfig as $policyGroup => $rules) {
            if ($rules['action'] === 'none') {
                continue;
            }

            $cutoffDate = now()->subDays($rules['age_days']);

            $query = \App\Models\Document::where('created_at', '<', $cutoffDate)
                        ->where('status', '!=', 'ARCHIVED'); // Ignore already processed items

            // Apply specific filtering based on config
            if (isset($rules['types'])) {
                $query->whereIn('document_type', $rules['types']);
            }

            if (isset($rules['department_codes'])) {
                // Must join or subquery to physical file to find department code
                $query->whereHas('fileRecord.originatingDepartment', function ($q) use ($rules) {
                    $q->whereIn('code', $rules['department_codes']);
                });
            }

            $documentsToPrune = $query->get();

            foreach ($documentsToPrune as $doc) {
                if ($rules['action'] === 'soft_delete') {
                    $doc->delete(); // Soft deletes by default
                } elseif ($rules['action'] === 'archive_to_cold_storage') {
                    // This serves as an extensible stub for moving to S3 Glacier
                    $doc->update(['status' => 'ARCHIVED_COLD']);
                }

                $prunedCount++;
            }
        }

        $this->info("Completed. Pruned {$prunedCount} documents according to retention policies.");
    }
}
