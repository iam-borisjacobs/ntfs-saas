<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Status;
use App\Models\FileRecord;
use App\Models\FileMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SlaBreachCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the SLA evaluation command accurately flags delayed movements and routes escalating priorities.
     */
    public function test_sla_breach_evaluator_triggers_notifications_correctly()
    {
        // 1. Setup minimal baseline
        $dept = Department::create(['name' => 'Evaluation Team', 'code' => 'EV-01', 'is_active' => true]);
        
        $sender = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'SND-01']);
        $receiver = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'RCV-01']);
        
        $statusActive = Status::create(['name' => 'Active', 'is_terminal' => false]);
        
        $file1 = FileRecord::create([
            'originating_department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'uuid' => Str::uuid()->toString(),
            'title' => 'SLA File 1 (25h)',
            'file_reference_number' => 'SLA-1',
            'current_owner_id' => $sender->id,
            'status_id' => $statusActive->id
        ]);

        $file2 = FileRecord::create([
            'originating_department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'uuid' => Str::uuid()->toString(),
            'title' => 'SLA File 2 (50h)',
            'file_reference_number' => 'SLA-2',
            'current_owner_id' => $sender->id,
            'status_id' => $statusActive->id
        ]);

        // 2. Mock pending movements in the past
        $movementL1 = FileMovement::create([
            'file_id' => $file1->id,
            'from_user_id' => $sender->id,
            'from_department_id' => $dept->id,
            'to_user_id' => $receiver->id,
            'to_department_id' => $dept->id,
            'request_uuid' => Str::uuid()->toString(),
            'acknowledgment_status' => 'PENDING',
            'dispatched_at' => Carbon::now()->subHours(25)
        ]);

        $movementCritical = FileMovement::create([
            'file_id' => $file2->id,
            'from_user_id' => $sender->id,
            'from_department_id' => $dept->id,
            'to_user_id' => $receiver->id,
            'to_department_id' => $dept->id,
            'request_uuid' => Str::uuid()->toString(),
            'acknowledgment_status' => 'PENDING',
            'dispatched_at' => Carbon::now()->subHours(50)
        ]);

        // 3. Execution Phase
        $this->artisan('nama:evaluate-sla-breaches')
             ->assertSuccessful();

        // 4. Assertions
        $movementL1->refresh();
        $movementCritical->refresh();

        // The flags should be fully deployed
        $this->assertTrue($movementL1->escalation_flag, 'Failed to escalate 24h breach.');
        $this->assertTrue($movementCritical->escalation_flag, 'Failed to escalate 48h breach.');

        // Notification checks
        // User 2 should get 2 incoming notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $receiver->id,
            'type' => 'SLA_BREACH',
            'severity' => 'HIGH'
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $receiver->id,
            'type' => 'SLA_BREACH',
            'severity' => 'CRITICAL'
        ]);

        // User 1 (Sender) should get exactly ONE escalation alert because File2 breached the hard 48h limit
        $this->assertDatabaseHas('notifications', [
            'user_id' => $sender->id,
            'type' => 'ESCALATION_L1',
            'severity' => 'CRITICAL'
        ]);
        
        // Assert Sender didn't get an alert for the 25h file
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $sender->id,
            'severity' => 'HIGH',
            'type' => 'ESCALATION_L1'
        ]);
    }
}
