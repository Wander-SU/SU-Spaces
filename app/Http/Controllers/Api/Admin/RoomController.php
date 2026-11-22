<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use App\Models\RoomSelection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /** POST /api/admin/buildings/{building}/rooms */
    public function store(Request $request, Building $building)
    {
        $data = $this->validated($request, $building);
        $room = $building->rooms()->create($this->attributes($data));

        return response()->json(['id' => $room->id, 'name' => $room->room_name], 201);
    }

    /** PUT /api/admin/rooms/{room} */
    public function update(Request $request, Room $room)
    {
        $data = $this->validated($request, $room->building, $room);
        $room->update($this->attributes($data));

        return response()->json(['id' => $room->id, 'name' => $room->room_name]);
    }

    /** DELETE /api/admin/rooms/{room}  (soft delete: past selections stay in the reports) */
    public function destroy(Room $room)
    {
        $active = RoomSelection::where('room_id', $room->id)->active()->count();
        if ($active > 0) {
            return response()->json([
                'message' => "This room has {$active} active " . ($active === 1 ? 'selection' : 'selections')
                    . '. Try again once they have ended.',
            ], 409);
        }

        $room->delete();

        return response()->json(['deleted' => true]);
    }

    private function validated(Request $request, Building $building, ?Room $room = null): array
    {
        $areas = config('spaces.areas.' . $building->building_name, []);

        return $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('rooms', 'room_name')
                    ->where('building_id', $building->id)
                    ->whereNull('deleted_at')
                    ->ignore($room?->id),
            ],
            'code' => ['nullable', 'string', 'max:40'],
            'floor' => ['nullable', 'string', 'max:40'],       // "Floor 2", "Ground Floor", "Basement"
            'area' => ['nullable', 'string', 'max:60', $areas ? Rule::in($areas) : 'prohibited'],
            'capacity' => ['required', 'integer', 'min:0', 'max:5000'],
            'type' => ['required', Rule::in(array_keys(config('spaces.types')))],
        ]);
    }

    private function attributes(array $data): array
    {
        $floorText = strtolower($data['floor'] ?? '');
        $floor = match (true) {
            str_contains($floorText, 'base') => -1,
            str_contains($floorText, 'ground') || $floorText === '' => 0,
            default => (int) preg_replace('/\D+/', '', $floorText),
        };

        return [
            'room_name' => $data['name'],
            'code' => ($data['code'] ?? null) ?: null,
            'floor' => $floor,
            'area' => ($data['area'] ?? null) ?: null,
            'capacity' => $data['capacity'],
            'type' => $data['type'],
        ];
    }
}