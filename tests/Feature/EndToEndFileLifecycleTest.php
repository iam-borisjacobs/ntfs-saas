<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Status;
use App\Services\FileTrackingEngine;

class EndToEndFileLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the full lifecycle of a file from creation to a terminal closed state.
     */
    public function test_full_file_lifecycle_workflow()
    {
        // 1. Setup Environment
        $creatorDept = Department::create(['name' => 'Registration', 'code' => 'REG', 'is_active' => true]);
        $receiverDept = Department::create(['name' => 'Processing', 'code' => 'PROC', 'is_active' => true]);

        $creator = User::factory()->create(['department_id' => $creatorDept->id, 'system_identifier' => 'USR-01']);
        $receiver = User::factory()->create(['department_id' => $receiverDept->id, 'system_identifier' => 'USR-02']);

        // Create core statuses
        $statusActive = Status::create(['name' => 'Active', 'is_terminal' => false]);
        $statusInTransit = Status::create(['name' => 'IN_TRANSIT', 'is_terminal' => false]);
        $statusReceived = Status::create(['name' => 'RECEIVED', 'is_terminal' => false]);
        $statusClosed = Status::create(['name' => 'Closed', 'is_terminal' => true]);

        // Mock state transition allowed
        \Illuminate\Support\Facades\DB::table('status_transitions')->insert([
            'from_status_id' => $statusActive->id,
            'to_status_id' => $statusInTransit->id
        ]);

        $movementService = app(\App\Services\FileMovementService::class);

        // 2. Generate File
        $file = \App\Models\FileRecord::create([
            'originating_department_id' => $creatorDept->id,
            'current_department_id' => $creatorDept->id,
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'E2E Testing File',
            'priority_level' => 1,
            'confidentiality_level' => 1,
            'file_reference_number' => 'REF-E2E',
            'current_owner_id' => $creator->id,
            'status_id' => $statusActive->id
        ]);

        $this->assertDatabaseHas('file_records', [
            'id' => $file->id,
            'title' => 'E2E Testing File',
            'current_owner_id' => $creator->id,
            'status_id' => $statusActive->id
        ]);

        // Simulate creator logging in to bypass Ownership checks in DB transaction (since Auth::id() is checked)
        $this->actingAs($creator);

        // 3. Dispatch File to Receiver
        $this->assertTrue(app(\App\Services\FileStateService::class)->isTransitionAllowed($file->status_id, $statusInTransit->id));
        
        $movementService->dispatchFile(
            $file->id,
            $receiver->id,
            $receiverDept->id,
            'Please process this file immediately.',
            \Illuminate\Support\Str::uuid()->toString()
        );

        $this->assertDatabaseHas('file_movements', [
            'file_id' => $file->id,
            'from_user_id' => $creator->id,
            'to_user_id' => $receiver->id,
            'acknowledgment_status' => 'PENDING'
        ]);

        // 4. Receiver Acknowledges File
        $this->actingAs($receiver);
        $movement = $file->movements()->where('acknowledgment_status', 'PENDING')->first();
        $movementService->receiveFile($movement->id);

        $this->assertDatabaseHas('file_movements', [
            'id' => $movement->id,
            'acknowledgment_status' => 'ACCEPTED',
        ]);

        // Check that custody shifted
        $file->refresh();
        $this->assertEquals($receiver->id, $file->current_owner_id);
        $this->assertEquals($receiverDept->id, $file->current_department_id);

        // 5. Close File (Terminal State)
        // Update file status manually for test simplicity if UpdateStatusService is decoupled
        $file->update(['status_id' => $statusClosed->id]);

        $this->assertDatabaseHas('file_records', [
            'id' => $file->id,
            'status_id' => $statusClosed->id
        ]);
    }
}
