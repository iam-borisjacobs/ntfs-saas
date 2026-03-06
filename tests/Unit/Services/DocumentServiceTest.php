<?php

namespace Tests\Unit\Services;

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentService $documentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentService = app(DocumentService::class);

        // Mock the digital_module disk to avoid real file I/O during tests
        Storage::fake('local');

        \App\Models\Status::firstOrCreate(['id' => 1], ['name' => 'ACTIVE', 'description' => 'Active Test State']);
    }

    /**
     * Test that a document can be successfully stored and attached to a movement.
     */
    public function test_document_is_stored_and_versioned_correctly()
    {
        $department = Department::create(['name' => 'IT Department', 'code' => 'IT01', 'is_active' => true]);
        $user = User::factory()->create(['department_id' => $department->id, 'system_identifier' => 'SYS-101']);

        $fileRecord = FileRecord::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'originating_department_id' => $department->id,
            'current_department_id' => $department->id,
            'title' => 'Test Document Record',
            'priority_level' => 1,
            'confidentiality_level' => 1,
            'file_reference_number' => 'REF-123',
            'current_owner_id' => $user->id,
            'status_id' => \App\Models\Status::first()->id,
        ]);

        $movement = FileMovement::create([
            'file_id' => $fileRecord->id,
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'from_department_id' => $department->id,
            'to_department_id' => $department->id,
            'movement_type' => 'CREATION',
            'acknowledgment_status' => 'ACCEPTED',
            'request_uuid' => Str::uuid()->toString(),
        ]);

        // Create a fake PDF
        $fakeFile = UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf');

        $metadata = [
            'file_id' => $fileRecord->id,
            'movement_id' => $movement->id,
            'document_type' => 'PRIMARY',
        ];

        $document = $this->documentService->storeDocument($fakeFile, $metadata, $user);

        $this->assertNotNull($document);
        $this->assertEquals('test-document.pdf', $document->file_name);
        $this->assertEquals('PRIMARY', $document->document_type);

        // Check attachment to movement
        $this->assertEquals($movement->id, $document->movement_id);

        // Check that NO historical version is created for a brand new upload
        $this->assertEquals(0, \App\Models\DocumentVersion::count());
        $this->assertEquals(1, $document->version_number);
        $this->assertEquals($user->id, $document->uploaded_by);

        // Assert the file was actually saved to the fake disk
        Storage::disk(config('digital_module.disk', 'local'))->assertExists($document->file_path);
    }

    /**
     * Test document validation thresholds are enforced via Service Logic if applicable.
     * Assuming standard limits are bound to the upload processing logic.
     */
    public function test_subsequent_document_upload_increments_version()
    {
        $department = Department::create(['name' => 'IT Department', 'code' => 'IT02', 'is_active' => true]);
        $user = User::factory()->create(['department_id' => $department->id, 'system_identifier' => 'SYS-102']);

        $fileRecord = FileRecord::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'originating_department_id' => $department->id,
            'current_department_id' => $department->id,
            'title' => 'Test Document Record 2',
            'priority_level' => 1,
            'confidentiality_level' => 1,
            'file_reference_number' => 'REF-124',
            'current_owner_id' => $user->id,
            'status_id' => \App\Models\Status::first()->id,
        ]);

        $movement = FileMovement::create([
            'file_id' => $fileRecord->id,
            'from_department_id' => $department->id,
            'to_department_id' => $department->id,
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'movement_type' => 'CREATION',
            'acknowledgment_status' => 'ACCEPTED',
            'request_uuid' => Str::uuid()->toString(),
        ]);

        $fakeFile1 = UploadedFile::fake()->create('doc-v1.pdf', 50, 'application/pdf');
        $fakeFile2 = UploadedFile::fake()->create('doc-v2.pdf', 60, 'application/pdf');

        $metadata = [
            'file_id' => $fileRecord->id,
            'movement_id' => $movement->id,
            'document_type' => 'SUPPORTING',
        ];

        // Upload version 1
        $document1 = $this->documentService->storeDocument($fakeFile1, $metadata, $user);
        $this->assertEquals(0, $document1->versions()->count());
        $this->assertEquals(1, $document1->version_number);

        // Upload version 2, explicitly passing the existing document to update it
        $document2 = $this->documentService->storeDocument($fakeFile2, $metadata, $user, $document1);

        // Refresh document
        $this->assertEquals(1, $document2->versions()->count());
        $versions = $document2->versions()->get();
        $this->assertEquals(1, $versions[0]->version_number);
        $this->assertEquals(2, $document2->version_number);
        $this->assertEquals($document1->id, $document2->id); // Ensure the core document record remained the same
    }
}
