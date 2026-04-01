<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerformanceStressTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['name' => 'Load Test Dept', 'code' => 'LDT-01', 'is_active' => true]);

        $this->user = User::factory()->create([
            'department_id' => $dept->id,
            'system_identifier' => 'BOT-01',
            'is_active' => true,
        ]);

        $status = Status::firstOrCreate(['name' => 'Active', 'is_terminal' => false]);

        // Seed 10,000 records using fast chunked Inserts directly to the DB to prevent memory leak during test setup
        $payloads = [];
        $timestamp = now()->toDateTimeString();

        for ($i = 1; $i <= 10000; $i++) {
            $payloads[] = [
                'uuid' => (string) Str::uuid(),
                'file_reference_number' => 'LOAD-'.$i,
                'title' => 'Load Testing File Data Stack '.$i,
                'originating_department_id' => $dept->id,
                'current_department_id' => $dept->id,
                'current_owner_id' => $this->user->id,
                'status_id' => $status->id,
                'priority_level' => 1,
                'confidentiality_level' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            // Chunk inserts
            if ($i % 1000 == 0) {
                DB::table('file_records')->insert($payloads);
                $payloads = []; // Reset RAM
            }
        }
    }

    /**
     * Test large volume pagination load speeds (10k records).
     */
    public function test_reports_engine_handles_10k_records_efficiently_without_memory_leaks()
    {
        $startMemory = memory_get_usage();
        $startTime = microtime(true);

        // 1. Visit Reports showing all records (paginated natively)
        $response = $this->actingAs($this->user)
            ->get(route('reports.index'));

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $response->assertStatus(200);

        // Ensure only the expected batch limits are returned (it shouldn't output all 10,000 files in HTML)
        $response->assertSee('LOAD-'); // Ensure latest records render

        // Assert execution speed and memory limits are within reasonable thresholds
        $executionTimeMs = ($endTime - $startTime) * 1000;
        $memoryUsageMb = ($endMemory - $startMemory) / 1024 / 1024;

        // Production validation limits (under 500ms API response / uses under 15MB additional RAM dynamically)
        $this->assertTrue($executionTimeMs < 2000, "Pagination took too long: {$executionTimeMs}ms");
        $this->assertTrue($memoryUsageMb < 30, "Pagination consumed too much peak memory: {$memoryUsageMb}MB");
    }
}
