<?php

namespace Database\Seeders;

use App\Models\Phase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Empty the table first before running
        Phase::truncate();

        // Create the phases
        $phase1 = new Phase();
        $phase1->phase_name = 'Phase 1';
        $phase1->save();

        $phase2 = new Phase();
        $phase2->phase_name = 'Phase 2';
        $phase2->save();
    }
}
