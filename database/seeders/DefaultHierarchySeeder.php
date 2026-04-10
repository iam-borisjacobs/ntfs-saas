<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultHierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $station = \App\Models\Station::where('name', 'Abuja Headquarters')->first();

        if (!$station) {
            $this->command->error('Abuja Headquarters station not found. Please run StationSeeder first.');
            return;
        }

        // 1. Root Node: MD
        $md = \App\Models\Department::firstOrCreate(
            ['name' => 'MD / CEO Office'],
            ['code' => 'MD-CEO', 'station_id' => $station->id, 'parent_id' => null]
        );

        // 2. Level 1: Directors
        $directors = [
            'ATS' => ['name' => 'Director of Air Traffic Services (ATS)', 'code' => 'DIR-ATS'],
            'ENG' => ['name' => 'Director of Engineering Services', 'code' => 'DIR-ENG'],
            'SEES' => ['name' => 'Director of Safety Electronics & Engineering Services (SEES)', 'code' => 'DIR-SEES'],
            'FIN' => ['name' => 'Director of Finance & Accounts', 'code' => 'DIR-FIN'],
            'HR' => ['name' => 'Director of Human Resources & Administration', 'code' => 'DIR-HR'],
            'COM' => ['name' => 'Director of Commercial & Business Development', 'code' => 'DIR-COM'],
            'LEG' => ['name' => 'Director of Legal Services', 'code' => 'DIR-LEG'],
        ];

        $directorModels = [];
        foreach ($directors as $key => $data) {
            $directorModels[$key] = \App\Models\Department::firstOrCreate(
                ['name' => $data['name'], 'station_id' => $station->id],
                ['code' => $data['code'], 'parent_id' => $md->id]
            );
        }

        // 3. Level 2: General Managers
        $gms = [
            'ATS' => [
                ['name' => 'GM, Area Control Center (ACC)', 'code' => 'GM-ACC'],
                ['name' => 'GM, Approach Control', 'code' => 'GM-APP'],
                ['name' => 'GM, Tower Operations', 'code' => 'GM-TWR'],
                ['name' => 'GM, Aeronautical Information Services (AIS)', 'code' => 'GM-AIS'],
            ],
            'ENG' => [
                ['name' => 'GM, Radar Systems', 'code' => 'GM-RADAR'],
                ['name' => 'GM, Communication Systems', 'code' => 'GM-COMM'],
                ['name' => 'GM, Navigation Systems', 'code' => 'GM-NAV'],
                ['name' => 'GM, Power & Infrastructure', 'code' => 'GM-PWR'],
            ],
            'SEES' => [
                ['name' => 'GM, Quality Assurance', 'code' => 'GM-QA'],
                ['name' => 'GM, Safety Oversight', 'code' => 'GM-SAFETY'],
                ['name' => 'GM, Compliance Monitoring', 'code' => 'GM-COMP'],
            ],
            'HR' => [
                ['name' => 'GM, Training', 'code' => 'GM-TRAIN'],
                ['name' => 'GM, Personnel Management', 'code' => 'GM-PERS'],
            ],
            'FIN' => [
                ['name' => 'GM, Accounts', 'code' => 'GM-ACCT'],
                ['name' => 'GM, Budget & Planning', 'code' => 'GM-BUDGET'],
            ],
        ];

        foreach ($gms as $directorKey => $gmList) {
            $parentDirector = $directorModels[$directorKey];
            foreach ($gmList as $data) {
                \App\Models\Department::firstOrCreate(
                    ['name' => $data['name'], 'station_id' => $station->id],
                    ['code' => $data['code'], 'parent_id' => $parentDirector->id]
                );
            }
        }
    }
}
