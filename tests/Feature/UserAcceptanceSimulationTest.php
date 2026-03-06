<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAcceptanceSimulationTest extends TestCase
{
    use RefreshDatabase;

    private $deptA;

    private $deptB;

    private $officerA;

    private $supervisorB;

    private $statusActive;

    private $statusClosed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptA = Department::create(['name' => 'Registry (Dept A)', 'code' => 'REG-A', 'is_active' => true]);
        $this->deptB = Department::create(['name' => 'Legal (Dept B)', 'code' => 'LEG-B', 'is_active' => true]);

        $officerRole = Role::firstOrCreate(['name' => 'Officer']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);

        $this->officerA = User::factory()->create([
            'department_id' => $this->deptA->id,
            'system_identifier' => 'UAT-OFF-A',
            'is_active' => true,
            'clearance_level' => 1,
        ]);
        $this->officerA->assignRole($officerRole);

        $this->supervisorB = User::factory()->create([
            'department_id' => $this->deptB->id,
            'system_identifier' => 'UAT-SUP-B',
            'is_active' => true,
            'clearance_level' => 3,
        ]);
        $this->supervisorB->assignRole($supervisorRole);

        $this->statusActive = Status::firstOrCreate(['name' => 'Active', 'is_terminal' => false]);
        $this->statusClosed = Status::firstOrCreate(['name' => 'CLOSED', 'is_terminal' => true]);
        $statusReceived = Status::firstOrCreate(['name' => 'RECEIVED', 'is_terminal' => false]);
        $statusInTransit = Status::firstOrCreate(['name' => 'IN_TRANSIT', 'is_terminal' => false]);

        \Illuminate\Support\Facades\DB::table('status_transitions')->insertOrIgnore([
            ['from_status_id' => $statusReceived->id, 'to_status_id' => $statusInTransit->id],
            ['from_status_id' => $statusInTransit->id, 'to_status_id' => $statusReceived->id],
            ['from_status_id' => $statusReceived->id, 'to_status_id' => $this->statusClosed->id],
        ]);
    }

    /**
     * E2E Simulation: Officer -> Supervisor -> Add Document -> Officer -> Close
     */
    public function test_full_lifecycle_multi_actor_ping_pong_simulation()
    {
        // 1. Officer A creates a file
        $response = $this->actingAs($this->officerA)
            ->post(route('files.store'), [
                'department_id' => $this->deptA->id,
                'title' => 'UAT Cross-Department Review',
                'description' => 'A file requiring Legal approval',
                'priority_level' => 2,
                'confidentiality_level' => 1,
            ]);
        $response->assertStatus(302);

        $file = FileRecord::where('title', 'UAT Cross-Department Review')->first();
        $this->assertNotNull($file);
        $this->assertEquals($this->officerA->id, $file->current_owner_id);

        // 2. Officer A dispatches file to Dept B (Supervisor B)
        $response = $this->actingAs($this->officerA)
            ->post(route('files.dispatch.store', $file), [
                'to_department_id' => $this->deptB->id,
                'to_user_id' => $this->supervisorB->id,
                'remarks' => 'Please review this urgently.',
            ]);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $movementToB = FileMovement::where('file_id', $file->id)->where('movement_type', 'DISPATCH')->latest('id')->first();
        $this->assertEquals('PENDING', $movementToB->acknowledgment_status);

        // 3. Supervisor B acknowledges receipt
        $response = $this->actingAs($this->supervisorB)
            ->post(route('movements.receive', $movementToB));
        $response->assertStatus(302);

        $file->refresh();
        $movementToB->refresh();
        $this->assertEquals('ACCEPTED', $movementToB->acknowledgment_status);
        $this->assertEquals($this->supervisorB->id, $file->current_owner_id);

        // 4. Supervisor B attaches a digital document (memo)
        config(['digital_module.enabled' => true]);
        Storage::fake('local');
        $digitalAttachment = UploadedFile::fake()->create('approval_memo.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->supervisorB)
            ->post(route('documents.store', $file), [
                'document' => $digitalAttachment,
                'document_type' => 'Memo',
            ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('documents', ['file_id' => $file->id, 'document_type' => 'Memo']);

        // 5. Supervisor B dispatches back to Dept A
        $response = $this->actingAs($this->supervisorB)
            ->post(route('files.dispatch.store', $file), [
                'to_department_id' => $this->deptA->id,
                'to_user_id' => $this->officerA->id,
                'remarks' => 'Reviewed and approved. See attached memo.',
            ]);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $movementToA = FileMovement::where('file_id', $file->id)->where('movement_type', 'DISPATCH')->latest('id')->first();

        // 6. Officer A acknowledges and closes the file
        $this->actingAs($this->officerA)
            ->post(route('movements.receive', $movementToA));

        $file->refresh();
        $this->assertEquals($this->officerA->id, $file->current_owner_id);

        $response = $this->actingAs($this->officerA)
            ->put(route('files.update', $file), [
                'status_id' => $this->statusClosed->id,
                'title' => $file->title,
                'department_id' => $file->originating_department_id,
                'priority_level' => $file->priority_level,
                'confidentiality_level' => $file->confidentiality_level,
            ]);

        $response->assertStatus(302);
        $file->refresh();
        $this->assertEquals($this->statusClosed->id, $file->status_id);
    }
}
