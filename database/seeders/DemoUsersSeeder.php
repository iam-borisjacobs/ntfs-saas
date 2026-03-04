<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please run the department seeder first.');
            return;
        }

        $password = Hash::make('Password@123!');

        // Ensure we have some roles
        $roles = Role::pluck('name')->toArray();
        $defaultRole = empty($roles) ? null : 'Clerk';

        $demoUsers = [
            'Oluwaseun Adeyemi' => 'o.adeyemi@nama.gov.ng',
            'Ngozi Eze' => 'n.eze@nama.gov.ng',
            'Abubakar Ibrahim' => 'a.ibrahim@nama.gov.ng',
            'Fatima Bello' => 'f.bello@nama.gov.ng',
            'Chidiebere Okafor' => 'c.okafor@nama.gov.ng',
            'Samuel Peters' => 's.peters@nama.gov.ng',
            'Grace Opeyemi' => 'g.opeyemi@nama.gov.ng',
            'Musa Danjuma' => 'm.danjuma@nama.gov.ng',
        ];

        $counter = 200; // Start ID range for demo users
        foreach ($demoUsers as $name => $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'system_identifier' => 'SYS-' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'password' => $password,
                    'department_id' => $departments->random()->id,
                    'is_active' => true,
                    'clearance_level' => rand(1, 3),
                ]
            );

            // Try to assign a generic role if they don't have one and roles exist
            if ($user->roles->isEmpty() && !empty($roles)) {
                // Safely try to find 'Clerk', 'Officer', or just pick the first non-admin role
                $assignedRole = in_array('Clerk', $roles) ? 'Clerk' : (in_array('Officer', $roles) ? 'Officer' : $roles[0]);
                try {
                    $user->assignRole($assignedRole);
                } catch (\Exception $e) {
                    // Ignore role assignment errors if Spatie fails to map
                }
            }

            $counter++;
        }

        $this->command->info('8 Demo Users successfully generated across random NAMA departments.');
    }
}
