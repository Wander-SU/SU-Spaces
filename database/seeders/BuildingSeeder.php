<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove all the data on buildings before seeding
        Building::truncate();

        // Create the buildings
        $stmb = new Building();
        $stmb->building_name = 'Sir Thomas Moore Building';
        $stmb->building_abbrev = 'STMB';
        $stmb->phase_id = 2;
        $stmb->save();

        // Management Sciences Building (MSB)
        $msb = new Building();
        $msb->building_name = 'Management Sciences Building';
        $msb->building_abbrev = 'MSB';
        $msb->phase_id = 2; 
        $msb->save();

        // Student Center (STC)
        $stc = new Building();
        $stc->building_name = 'Student Center';
        $stc->building_abbrev = 'STC';
        $stc->phase_id = 2; // 
        $stc->save();

        // Library
        $library = new Building();
        $library->building_name = 'University Library';
        $library->building_abbrev = 'MainLib';
        $library->phase_id = 2; 
        $library->save();

        // Central Block (CB)
        $cb = new Building();
        $cb->building_name = 'Central Building';
        $cb->building_abbrev = 'CB';
        $cb->phase_id = 1;
        $cb->save();

        // Strathmore Business School (SBS)
        $sbs = new Building();
        $sbs->building_name = 'Strathmore Business School';
        $sbs->building_abbrev = 'SBS';
        $sbs->phase_id = 2; 
        $sbs->save();

        // Oval Building (SBS)
        $OB = new Building();
        $OB->building_name = 'Oval Building';
        $OB->building_abbrev = 'OB';
        $OB->phase_id = 2; 
        $OB->save();

        // Strathmore Engineering Labs
        $serc = new Building();
        $serc->building_name = "Strathmore Engineering Labs";
        $serc->building_abbrev = "SERC";
        $serc->phase_id= 2;
        $serc->save();

    }
}
