<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;

class BuildingController extends Controller
{
    /** GET /api/buildings */
    public function index()
    {
        // Returns building data, ordered by name = gives the id and the name
        return response()->json([
            'data' => Building::orderBy('building_name','desc')->get(['id', 'building_name']),
        ]);
    }
}
