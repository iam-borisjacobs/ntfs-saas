<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    private $department;

    private $clerk;

    private $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Records Dept', 'code' => 'REC', 'is_active' => true]);

        // Setup Roles
        $clerkRole = Role::firstOrCreate(['name' => 'Clerk']);
        $officerRole = Role::firstOrCreate(['name' => 'Officer']);

        // Setup Users
        $this->clerk = User::factory()->create([
            'department_id' => $this->department->id,
            'system_identifier' => 'CLK-01',
            'clearance_level' => 1,
        ]);
        $this->clerk->assignRole($clerkRole);

        $this->officer = User::factory()->create([
            'department_id' => $this->department->id,
            'system_identifier' => 'OFF-01',
            'clearance_level' => 2,
        ]);
        $this->officer->assignRole($officerRole);

        Status::firstOrCreate(['name' => 'Active', 'is_terminal' => false]);
        Status::firstOrCreate(['name' => 'RECEIVED', 'is_terminal' => false]);
    }

    /**
     * Test a Clerk attempting to access the File Creation endpoint.
     */
    public function test_clerks_cannot_create_new_files()
    {
        $payload = [
            'department_id' => $this->department->id,
            'title' => 'Top Secret Exploitation File',
            'description' => 'A file created by an unauthorized user',
            'priority_level' => 1,
            'confidentiality_level' => 1,
        ];

        // 1. Clerk attempts API hit
        $response = $this->actingAs($this->clerk)
            ->post(route('files.store'), $payload);

        // Should be strictly forbidden
        $response->assertForbidden();

        $this->assertDatabaseMissing('file_records', [
            'title' => 'Top Secret Exploitation File',
        ]);
    }

    /**
     * Test an Officer successfully accessing File Creation endpoint.
     */
    public function test_officers_can_create_new_files()
    {
        $payload = [
            'department_id' => $this->department->id,
            'title' => 'Legal Officer File',
            'description' => 'A file created legitimately',
            'priority_level' => 1,
            'confidentiality_level' => 1,
        ];

        // 1. Officer attempts API hit
        $response = $this->actingAs($this->officer)
                         ->post(route('files.store'), $payload);
                         
        $response->dumpSession();

        // Should successfully redirect back to dashboard or queue
        $response->assertStatus(302);

        $this->assertDatabaseHas('file_records', [
            'title' => 'Legal Officer File',
        ]);
    }
}
