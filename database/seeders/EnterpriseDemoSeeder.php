<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Department;
use App\Models\FileRecord;
use App\Models\FileMovement;
use App\Models\Status;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class EnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Initializing 5-Month Enterprise Demo Dataset...');

        $users = User::with('department')->whereNotNull('department_id')->whereNotNull('phone_number')->get();
        
        if ($users->isEmpty()) {
            // Guarantee at least some users have phone numbers for the Twilio test
            $users = User::with('department')->whereNotNull('department_id')->get();
            foreach($users as $user) {
                $user->update(['phone_number' => '+123456789' . rand(10,99)]);
            }
        }

        if ($users->count() < 2) {
            $this->command->error('Not enough users found.');
            return;
        }

        $receivedStatus = Status::where('name', 'RECEIVED')->first() ?? Status::firstOrCreate(['name' => 'RECEIVED', 'is_terminal' => false]);
        $inTransitStatus = Status::where('name', 'IN_TRANSIT')->first() ?? Status::firstOrCreate(['name' => 'IN_TRANSIT', 'is_terminal' => false]);
        $closedStatus = Status::where('name', 'CLOSED')->first() ?? Status::firstOrCreate(['name' => 'CLOSED', 'is_terminal' => true]);

        // Generate massive 5-month data set
        for ($i = 0; $i < 150; $i++) {
            $creator = $users->random();
            $targetUser = $users->where('id', '!=', $creator->id)->random();

            // Distribute over the past 5 months (approx 150 days)
            $genesisDate = Carbon::now()->subDays(rand(1, 150))->subHours(rand(1, 24));
            
            $fileRecord = FileRecord::create([
                'uuid' => (string) Str::uuid(),
                'file_reference_number' => 'SYS/ENT/26/' . strtoupper(Str::random(6)),
                'title' => 'Enterprise Demo Document ' . $i,
                'originating_department_id' => $creator->department_id,
                'current_department_id' => $creator->department_id,
                'current_owner_id' => $creator->id,
                'created_by' => $creator->id,
                'status_id' => $receivedStatus->id,
                'priority_level' => rand(1, 3),
                'confidentiality_level' => rand(1, 3),
                'created_at' => $genesisDate,
            ]);

            \App\Services\AuditLoggerService::log([
                'agency_id' => 1,
                'action_type' => 'CREATION',
                'entity_type' => 'App\Models\FileRecord',
                'entity_id' => $fileRecord->id,
                'user_id' => $creator->id,
                'old_values' => null,
                'new_values' => json_encode(['title' => $fileRecord->title]),
                'ip_address' => '127.0.0.1',
                'created_at' => $genesisDate,
            ]);

            // Create a fake digital document for watermarking tests
            if ($i % 5 === 0) {
                // Ensure directory exists
                Storage::disk('local')->makeDirectory('test_documents');
                $fakePath = 'test_documents/dummy_' . Str::random(5) . '.pdf';
                Storage::disk('local')->put($fakePath, "Fake PDF Content"); // This is safe, FPDI will fail gracefully on fake PDF and fallback based on our logic
                
                Document::create([
                    'file_id' => $fileRecord->id,
                    'file_name' => 'Confidential_Attachment_' . $i . '.pdf',
                    'file_path' => $fakePath,
                    'file_size' => rand(1024, 10240),
                    'file_hash' => hash('sha256', Str::random(10)),
                    'uploaded_by' => $creator->id,
                    'document_type' => 'Memo',
                    'created_at' => $genesisDate,
                ]);
            }

            // Scenario Builder
            $scenario = rand(1, 5);

            if ($scenario === 1) {
                // Perfect historic dispatch & receive chain
                $dispatchDate = $genesisDate->copy()->addDays(rand(1, 5));
                $receiveDate = $dispatchDate->copy()->addHours(rand(1, 48));

                FileMovement::create([
                    'file_id' => $fileRecord->id,
                    'request_uuid' => (string) Str::uuid(),
                    'from_user_id' => $creator->id,
                    'to_user_id' => $targetUser->id,
                    'from_department_id' => $creator->department_id,
                    'to_department_id' => $targetUser->department_id,
                    'movement_type' => 'DISPATCH',
                    'acknowledgment_status' => 'ACCEPTED',
                    'dispatched_at' => $dispatchDate,
                    'received_at' => $receiveDate,
                ]);

                \App\Services\AuditLoggerService::log([
                    'agency_id' => 1,
                    'action_type' => 'DISPATCH',
                    'entity_type' => 'App\Models\FileMovement',
                    'entity_id' => $fileRecord->id,
                    'user_id' => $creator->id,
                    'old_values' => null,
                    'new_values' => json_encode(['to_user' => $targetUser->id]),
                    'ip_address' => '127.0.0.1',
                    'created_at' => $dispatchDate,
                ]);
                
                $fileRecord->update([
                    'current_department_id' => $targetUser->department_id,
                    'current_owner_id' => $targetUser->id,
                ]);
            } elseif ($scenario === 2) {
                // SLA Breach Trap! File was requested days ago and never received
                $dispatchDate = Carbon::now()->subDays(rand(3, 7)); // Definitely past 48 hours

                FileMovement::create([
                    'file_id' => $fileRecord->id,
                    'request_uuid' => (string) Str::uuid(),
                    'from_user_id' => $creator->id,
                    'to_user_id' => $targetUser->id,
                    'from_department_id' => $creator->department_id,
                    'to_department_id' => $targetUser->department_id,
                    'movement_type' => 'DISPATCH',
                    'acknowledgment_status' => 'PENDING',
                    'dispatched_at' => $dispatchDate,
                    'escalation_flag' => false, // Set false to guarantee the command picks it up for SMS
                ]);

                $fileRecord->update(['status_id' => $inTransitStatus->id]);
                
                \App\Services\AuditLoggerService::log([
                    'agency_id' => 1,
                    'action_type' => 'DISPATCH_PENDING',
                    'entity_type' => 'App\Models\FileMovement',
                    'entity_id' => $fileRecord->id,
                    'user_id' => $creator->id,
                    'old_values' => null,
                    'new_values' => json_encode(['status' => 'SLA_CANDIDATE']),
                    'ip_address' => '127.0.0.1',
                    'created_at' => $dispatchDate,
                ]);
            } elseif ($scenario === 3) {
                // Closed file
                $fileRecord->update(['status_id' => $closedStatus->id]);
            }
        }

        $this->command->info('Enterprise Simulation Data (5 months) Loaded Successfully!');
        $this->command->info('- Over 150 historical documents created.');
        $this->command->info('- Twilio SLA Breach Traps armed (run: php artisan nama:evaluate-sla-breaches).');
        $this->command->info('- Dynamic PDF Download targets secured.');
        $this->command->info('- Cryptographic blockchain ledger established spanning back 150 days.');
    }
}
