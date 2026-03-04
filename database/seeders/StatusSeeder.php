<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'name' => 'REGISTERED', 'is_terminal' => false],
            ['id' => 2, 'name' => 'IN_TRANSIT', 'is_terminal' => false],
            ['id' => 3, 'name' => 'RECEIVED', 'is_terminal' => false],
            ['id' => 4, 'name' => 'PENDING_REVIEW', 'is_terminal' => false],
            ['id' => 5, 'name' => 'REJECTED', 'is_terminal' => false],
            ['id' => 6, 'name' => 'ARCHIVED', 'is_terminal' => true],
            ['id' => 7, 'name' => 'CLOSED', 'is_terminal' => true],
        ];

        foreach ($statuses as $status) {
            \Illuminate\Support\Facades\DB::table('statuses')->updateOrInsert(['id' => $status['id']], $status);
        }
        
        // Also populate default transitions for Phase 5 to avoid null transition failures
        $transitions = [
            ['from_status_id' => 1, 'to_status_id' => 2], // Registered -> In Transit
            ['from_status_id' => 2, 'to_status_id' => 3], // In Transit -> Received
            ['from_status_id' => 2, 'to_status_id' => 5], // In Transit -> Rejected
            ['from_status_id' => 3, 'to_status_id' => 4], // Received -> Pending Review
            ['from_status_id' => 4, 'to_status_id' => 2], // Pending Review -> In Transit (Routing elsewhere)
            ['from_status_id' => 4, 'to_status_id' => 7], // Pending Review -> Closed
            ['from_status_id' => 7, 'to_status_id' => 6], // Closed -> Archived
            ['from_status_id' => 5, 'to_status_id' => 2], // Rejected -> In Transit (Re-dispatch)
            ['from_status_id' => 3, 'to_status_id' => 2], // Received -> In Transit (Dispatch somewhere else without review)
        ];
        
        foreach ($transitions as $transition) {
            \Illuminate\Support\Facades\DB::table('status_transitions')->updateOrInsert($transition, $transition);
        }
    }
}
