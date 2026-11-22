<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomDetailsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Room::all() as $room) {
            $room->floor = $this->floorFromName($room->room_name);
            $room->area = $this->areaOverrides[$room->room_name] ?? null;
            $room->save();   // type keeps its default of 'room'
        }
    }
    /** Area within a building: 'Room name' => 'Left wing' etc. */
    private array $areaOverrides = [
        // 'LT 1' => 'Left wing',
    ];

    private function floorFromName(string $name): int
    {
        if (preg_match('/\bB-/i', $name)) return -1;          // basement
        if (preg_match('/\bGF-/i', $name)) return 0;          // ground floor
        if (preg_match('/\bF(\d+)-/i', $name, $m)) return (int) $m[1];
        return 0;                                             // special rooms like "Usawa"
    }
}