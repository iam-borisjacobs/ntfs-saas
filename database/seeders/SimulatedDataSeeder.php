<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\FileJacket;
use App\Models\FileRecord;
use App\Models\Reminder;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulatedDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::with('department')->whereNotNull('department_id')->get();
        if ($users->count() < 2) {
            $this->command->error('Not enough users found.');
            return;
        }

        $receivedStatus = Status::where('name', 'RECEIVED')->first() ?? Status::firstOrCreate(['name' => 'RECEIVED', 'is_terminal' => false]);
        $inTransitStatus = Status::where('name', 'IN_TRANSIT')->first() ?? Status::firstOrCreate(['name' => 'IN_TRANSIT', 'is_terminal' => false]);
        $closedStatus = Status::where('name', 'CLOSED')->first() ?? Status::firstOrCreate(['name' => 'CLOSED', 'is_terminal' => true]);

        // 1. Create 5 File Jackets randomly distributed across active user departments
        $jackets = collect();
        for ($i = 1; $i <= 5; $i++) {
            $dept = Department::whereHas('users')->inRandomOrder()->first();
            $user = $dept->users()->inRandomOrder()->first();
            $jackets->push(
                FileJacket::create([
                    'department_id' => $dept->id,
                    'title' => 'Administrative File Volume ' . $i,
                    'jacket_code' => $dept->code . '/2026/00' . $i,
                    'status' => 'active',
                    'current_department_id' => $dept->id,
                    'current_holder_user_id' => $user->id,
                    'created_by' => $user->id,
                ])
            );
        }

        // 2. Generate 30 File Records
        $titles = [
            'Q1 Performance Review Metrics', 'Annual Leave Request - Technical', 'Station Equipment Procurement', 
            'Server Rack Relocation Memo', 'Safety Briefing Minutes', 'Employee Disciplinary Action', 
            'Financial Audit Summary', 'Vendor Payment Authorization', 'Security Clearance Renewal', 
            'Network Outage Incident Report', 'New Hire Onboarding Checklist', 'Strategic Implementation Guideline'
        ];

        for ($i = 0; $i < 30; $i++) {
            $creator = $users->random();
            $originDept = $creator->department;
            
            // Randomly select a state
            $case = rand(1, 4);

            $fileTitle = $titles[array_rand($titles)] . ' - ' . Str::random(4);
            
            $fileRecord = FileRecord::create([
                'uuid' => (string) Str::uuid(),
                'file_reference_number' => 'NAMA/2026/' . strtoupper(Str::random(6)),
                'title' => $fileTitle,
                'originating_department_id' => $originDept->id,
                'current_department_id' => $creator->department_id,
                'current_owner_id' => $creator->id,
                'created_by' => $creator->id,
                'status_id' => $receivedStatus->id,
                'priority_level' => rand(1, 3),
                'confidentiality_level' => rand(1, 3),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Genesis Creation Movement
            $fileRecord->movements()->create([
                'request_uuid' => (string) Str::uuid(),
                'from_user_id' => $creator->id,
                'to_user_id' => $creator->id,
                'from_department_id' => $originDept->id,
                'to_department_id' => $originDept->id,
                'movement_type' => 'CREATION',
                'remarks' => 'Initial File Generation',
                'acknowledgment_status' => 'ACCEPTED',
                'received_at' => $fileRecord->created_at,
                'dispatched_at' => $fileRecord->created_at,
            ]);

            // Apply different lifecycles based on random case
            if ($case === 2) {
                // DISPATCHED (In Transit)
                $targetUser = $users->where('id', '!=', $creator->id)->random();
                $fileRecord->update([
                    'status_id' => $inTransitStatus->id,
                ]);
                $fileRecord->movements()->create([
                    'request_uuid' => (string) Str::uuid(),
                    'from_user_id' => $creator->id,
                    'to_user_id' => $targetUser->id,
                    'from_department_id' => $creator->department_id,
                    'to_department_id' => $targetUser->department_id,
                    'movement_type' => 'DISPATCH',
                    'remarks' => 'Please review and advise',
                    'acknowledgment_status' => 'PENDING',
                    'dispatched_at' => now()->subHours(rand(1, 120)),
                ]);
            } elseif ($case === 3) {
                // RECEIVED & FILED INTO JACKET
                $targetUser = $users->where('id', '!=', $creator->id)->random();
                
                // Dispatch
                $dispatchDate = now()->subDays(rand(2, 10));
                $movement = $fileRecord->movements()->create([
                    'request_uuid' => (string) Str::uuid(),
                    'from_user_id' => $creator->id,
                    'to_user_id' => $targetUser->id,
                    'from_department_id' => $creator->department_id,
                    'to_department_id' => $targetUser->department_id,
                    'movement_type' => 'DISPATCH',
                    'remarks' => 'For your attention',
                    'acknowledgment_status' => 'PENDING',
                    'dispatched_at' => $dispatchDate,
                ]);

                // Receive
                $receiveDate = $dispatchDate->copy()->addHours(rand(1, 24));
                $movement->update([
                    'acknowledgment_status' => 'ACCEPTED',
                    'received_at' => $receiveDate,
                ]);

                // Find a jacket in target department to file into
                $bestJacket = $jackets->where('department_id', $targetUser->department_id)->first();

                $fileRecord->update([
                    'status_id' => $receivedStatus->id,
                    'current_department_id' => $targetUser->department_id,
                    'current_owner_id' => $targetUser->id,
                    'file_jacket_id' => $bestJacket ? $bestJacket->id : null,
                ]);

                // Add an active reminder for the target user to process this
                if (rand(1, 3) == 1) {
                    Reminder::create([
                        'user_id' => $targetUser->id,
                        'title' => 'Follow up on ' . $fileRecord->title,
                        'description' => 'Need to provide an update to HQ.',
                        'reminder_date' => now()->addDays(rand(1, 5))->format('Y-m-d'),
                        'is_completed' => false,
                    ]);
                }

            } elseif ($case === 4) {
                // CLOSED
                $fileRecord->update([
                    'status_id' => $closedStatus->id,
                ]);
            }
        }

        $this->command->info('Successfully populated realistic file jackets, generated files, dispatches, and active reminders.');
    }
}
