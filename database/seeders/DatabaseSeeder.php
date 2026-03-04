<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Setup initial structure
        $this->call(RolesAndPermissionsSeeder::class);

        // Core Departments
        $registryDept = \App\Models\Department::firstOrCreate(
            ['code' => 'REG', 'name' => 'Central Registry', 'agency_id' => 1]
        );
        
        $adminDept = \App\Models\Department::firstOrCreate(
            ['code' => 'ADM', 'name' => 'System Administration', 'agency_id' => 1]
        );

        // System Admin Account
        $sysAdminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@nama.gov.ng'],
            [
                'name' => 'System Administrator',
                'system_identifier' => 'SYS-001',
                'password' => \Illuminate\Support\Facades\Hash::make('123456789'),
                'department_id' => $adminDept->id,
                'clearance_level' => 3,
                'is_active' => true,
            ]
        );

        $sysAdminUser->assignRole('Sys Admin');
    }
}
