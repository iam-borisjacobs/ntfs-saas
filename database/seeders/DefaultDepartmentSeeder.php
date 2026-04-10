<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            // Directorate of Operations (DOO)
            ['name' => 'Air Traffic Control (ATC)', 'code' => 'DOO-ATC'],
            ['name' => 'Aeronautical Information Services (AIS)', 'code' => 'DOO-AIS'],
            ['name' => 'Search and Rescue (SAR)', 'code' => 'DOO-SAR'],
            ['name' => 'Air Traffic Management (ATM)', 'code' => 'DOO-ATM'],
            
            // Directorate of Safety Electronics and Engineering Services (DSEES)
            ['name' => 'Communications', 'code' => 'DSEES-COM'],
            ['name' => 'Navigation Aids (NavAids)', 'code' => 'DSEES-NAV'],
            ['name' => 'Surveillance', 'code' => 'DSEES-SUR'],
            ['name' => 'Electromechanical', 'code' => 'DSEES-EME'],
            
            // Directorate of Human Resources and Administration (DHR&A)
            ['name' => 'Personnel/Human Resources', 'code' => 'DHRA-HR'],
            ['name' => 'Training and Development', 'code' => 'DHRA-TD'],
            ['name' => 'General Administration', 'code' => 'DHRA-GA'],
            
            // Directorate of Finance, Accounts, and Budget (DFA/B)
            ['name' => 'Revenue', 'code' => 'DFAB-REV'],
            ['name' => 'Expenditure & Budget', 'code' => 'DFAB-EB'],
            ['name' => 'Audit', 'code' => 'DFAB-AUD'],
            
            // Specialized & Support Departments
            ['name' => 'Legal Services / Company Secretariat', 'code' => 'SUP-LEG'],
            ['name' => 'Public Affairs and Consumer Protection', 'code' => 'SUP-PAC'],
            ['name' => 'ICT (Information and Communication Technology)', 'code' => 'SUP-ICT'],
            ['name' => 'Procurement', 'code' => 'SUP-PRO'],
            ['name' => 'Safety Management Systems (SMS)', 'code' => 'SUP-SMS'],
        ];

        foreach ($departments as $dept) {
            \App\Models\Department::firstOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name']]
            );
        }
    }
}
