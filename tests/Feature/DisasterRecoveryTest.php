<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DisasterRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $department;

    private $fileRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Data Integrity Dept', 'code' => 'DI-01', 'is_active' => true]);

        $role = Role::firstOrCreate(['name' => 'Officer']);

        $this->user = User::factory()->create([
            'department_id' => $this->department->id,
            'system_identifier' => 'REC-01',
            'is_active' => true,
            'clearance_level' => 3,
        ]);
        $this->user->assignRole($role);

        $status = Status::firstOrCreate(['name' => 'Active', 'is_terminal' => false]);

        $this->fileRecord = FileRecord::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'file_reference_number' => 'DR-TEST-001',
            'title' => 'Disaster Recovery Baseline',
            'originating_department_id' => $this->department->id,
            'current_department_id' => $this->department->id,
            'current_owner_id' => $this->user->id,
            'status_id' => $status->id,
            'priority_level' => 1,
            'confidentiality_level' => 1,
        ]);
    }

    /**
     * Test Database Transaction Rollback on Storage Failure
     */
    public function test_database_transactions_rollback_on_filesystem_failure()
    {
        config(['digital_module.enabled' => true]);

        // Mock the storage disk to brutally throw an exception when putFileAs is called
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('putFileAs')->andThrow(new \Exception('Simulated Fatal Network Attached Storage (NAS) Disconnect'));

        $digitalAttachment = UploadedFile::fake()->create('top_secret_evidence.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('documents.store', $this->fileRecord), [
                'document' => $digitalAttachment,
                'document_type' => 'Attachment',
            ]);

        // It should gracefully catch the exception and redirect with an error message
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['document' => 'Upload failed securely. Simulated Fatal Network Attached Storage (NAS) Disconnect']);

        // FATAL CHECK: The document must NOT have been saved into the database if the disk unlinked.
        $this->assertDatabaseMissing('documents', [
            'file_id' => $this->fileRecord->id,
        ]);
    }
}
