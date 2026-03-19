<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $station = \App\Models\Station::create([
            'name' => 'Abuja Headquarters',
            'code' => 'ABV-HQ',
            'type' => 'Headquarters',
        ]);

        // Link all existing departments to this Station to preserve functionality
        \App\Models\Department::query()->update(['station_id' => $station->id]);
    }
}
