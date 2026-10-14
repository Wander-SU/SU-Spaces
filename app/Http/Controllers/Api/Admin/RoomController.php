<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /** POST /api/admin/buildings/{building}/rooms  {name, code, floor, capacity, type} */
    public function store(Request $request, Building $building)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:40'],
            'floor' => ['nullable', 'string', 'max:40'],   // app sends text like "Floor 2"
            'capacity' => ['required', 'integer', 'min:0', 'max:5000'],
            'type' => ['required', Rule::in(array_keys(config('spaces.types')))],
        ]);

        // dd($data);

        // Accept "Floor 2", "2", "Ground", "Basement" from the app's free-text field.
        $floorText = strtolower($data['floor'] ?? '');
        $floor = match (true) {
            str_contains($floorText, 'base') => -1,
            str_contains($floorText, 'ground') || $floorText === '' => 0,
            default => (int) preg_replace('/\D+/', '', $floorText),
        };

        $room = $building->rooms()->create([
            'room_name' => $data['name'],
            // 'code' => ($data['code'] ?? null) ?: null,
            // 'floor' => $floor,
            'capacity' => $data['capacity'],
            // 'type' => $data['type'],
        ]);

        return response()->json(['id' => $room->id, 'name' => $room->room_name], 201);
    }
}
