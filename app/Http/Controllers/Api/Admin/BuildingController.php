<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /** POST /api/admin/phases/{phase}/buildings  {name} */
    public function store(Request $request, Phase $phase)
    {
        $data = $request->validate([
            'building_name' => ['required', 'string', 'max:120'],
            'building_abbrev' => ['sometimes','string','max:20']
            ]);

        $building = $phase->buildings()->create($data);

        return response()->json(['id' => $building->id, 'name' => $building->building_name], 201);
    }
}
