<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Status;
use App\Models\FileRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConcurrencyStressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that double-submitting a dispatch event (e.g. user mashing the "Confirm" button)
     * gracefully returns the same movement instance instead of duplicating records.
     */
    public function test_dispatch_idempotency_prevents_duplicate_movements()
    {
        // Setup
        $dept = Department::create(['name' => 'Registration', 'code' => 'REG', 'is_active' => true]);
        $creator = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'USR-CON-01']);
        $receiver = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'USR-CON-02']);
        
        $statusActive = Status::create(['name' => 'Active', 'is_terminal' => false]);
        $statusInTransit = Status::create(['name' => 'IN_TRANSIT', 'is_terminal' => false]);
        
        DB::table('status_transitions')->insert([
            'from_status_id' => $statusActive->id,
            'to_status_id' => $statusInTransit->id
        ]);

        $file = FileRecord::create([
            'originating_department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'uuid' => Str::uuid()->toString(),
            'title' => 'Concurrency File',
            'file_reference_number' => 'REF-CONC-1',
            'current_owner_id' => $creator->id,
            'status_id' => $statusActive->id
        ]);

        $this->actingAs($creator);
        $movementService = app(\App\Services\FileMovementService::class);
        $requestUuid = Str::uuid()->toString();

        // First Dispatch
        $movementOne = $movementService->dispatchFile(
            $file->id,
            $receiver->id,
            $dept->id,
            'First click.',
            $requestUuid
        );

        // Immediate Double Dispatch using identical UUID
        $movementTwo = $movementService->dispatchFile(
            $file->id,
            $receiver->id,
            $dept->id,
            'Second accidental click.',
            $requestUuid
        );

        $this->assertEquals($movementOne->id, $movementTwo->id, 'Idempotency failed: generated a duplicate ID.');
        $this->assertEquals(1, $file->movements()->count(), 'Idempotency failed: multiple physical records exist.');
    }

    /**
     * Test that attempting to dispatch a file that is ALREADY pending acknowledgment
     * to a DIFFERENT user strictly throws a race-condition lock exception.
     */
    public function test_pending_acknowledgment_deadlock_prevention()
    {
        $dept = Department::create(['name' => 'Command', 'code' => 'CMD', 'is_active' => true]);
        $creator = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'USR-RACE-01']);
        $receiverOne = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'USR-RACE-02']);
        $receiverTwo = User::factory()->create(['department_id' => $dept->id, 'system_identifier' => 'USR-RACE-03']);
        
        $statusActive = Status::create(['name' => 'Active', 'is_terminal' => false]);
        $statusInTransit = Status::create(['name' => 'IN_TRANSIT', 'is_terminal' => false]);
        
        DB::table('status_transitions')->insert([
            'from_status_id' => $statusActive->id,
            'to_status_id' => $statusInTransit->id
        ]);

        $file = FileRecord::create([
            'originating_department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'uuid' => Str::uuid()->toString(),
            'title' => 'Race Condition File',
            'file_reference_number' => 'REF-CONC-2',
            'current_owner_id' => $creator->id,
            'status_id' => $statusActive->id
        ]);

        $this->actingAs($creator);
        $movementService = app(\App\Services\FileMovementService::class);

        // Valid Dispatch
        $movementService->dispatchFile($file->id, $receiverOne->id, $dept->id, 'Forwarding to User 2.', Str::uuid()->toString());

        // Malicious or concurrent dispatch before User 2 acknowledges
        $exceptionThrown = false;
        try {
            $movementService->dispatchFile($file->id, $receiverTwo->id, $dept->id, 'Trying to route around User 2.', Str::uuid()->toString());
        } catch (\Exception $e) {
            $this->assertStringContainsString('pending dispatch awaiting acknowledgment', $e->getMessage());
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown, 'Concurrency lock failed: Allowed secondary dispatch while file was locked in transit.');
    }
}
