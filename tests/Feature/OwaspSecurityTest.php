<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Status;
use App\Models\FileRecord;
use Spatie\Permission\Models\Role;

class OwaspSecurityTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $department;
    private $status;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->department = Department::create(['name' => 'Security Dept', 'code' => 'SEC-01', 'is_active' => true]);
        
        $role = Role::firstOrCreate(['name' => 'Officer']);
        
        $this->user = User::factory()->create([
            'department_id' => $this->department->id,
            'system_identifier' => 'SEC-OFF-01',
            'is_active' => true,
            'clearance_level' => 2
        ]);
        $this->user->assignRole($role);
        
        $this->status = Status::firstOrCreate(['name' => 'RECEIVED', 'is_terminal' => false]);
    }

    /**
     * Test XSS injection on File Title creation.
     */
    public function test_xss_injections_are_escaped_on_file_creation()
    {
        $xssPayload = '<script>alert("XSS")</script>';
        
        $payload = [
            'department_id' => $this->department->id,
            'title' => $xssPayload,
            'description' => 'Injecting a nefarious payload',
            'priority_level' => 1,
            'confidentiality_level' => 1
        ];

        // Ensure CSRF middleware is active (enabled by default in this environment)
        $this->actingAs($this->user)
             ->post(route('files.store'), $payload);
             
        // Get the generated file
        $file = FileRecord::latest()->first();
        $this->assertNotNull($file);
        
        // Ensure Database stores the raw input (Sanitization should happen on Output (Blade))
        $this->assertEquals($xssPayload, $file->title);

        $response = $this->get(route('files.show', $file));
        
        $response->assertStatus(200);
        
        // Assert the returned view escapes the HTML entities, blocking execution
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
        $response->assertDontSee($xssPayload, false);
    }
    
    /**
     * Test Session Fixation by ensuring token regeneration upon auth state changes.
     */
    public function test_session_fixation_protection_regenerates_tokens_on_login()
    {
        $response = $this->get('/login');
        $initialSessionId = $this->app['session']->getId();
        
        $this->assertNotEmpty($initialSessionId);
        
        // Perform standard login attempt
        $loginResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        
        $loginResponse->assertRedirect(route('dashboard'));
        
        $newSessionId = $this->app['session']->getId();
        
        // Assert the session ID changed
        $this->assertNotEquals($initialSessionId, $newSessionId);
    }
}
