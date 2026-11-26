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
            $room->room_description = $this->roomDescriptors[$room->room_name] ?? null;
            $room->save();   // type keeps its default of 'room'
        }
    }
    /** Area within a building: 'Room name' => 'Left wing' etc. */
    private array $areaOverrides = [
        'LT 1' => 'Left wing',
        'LT 2' => 'Left wing',
        'LT 3' => 'Left wing',
        'LT 4' => 'Left wing',
        'LT 5' => 'Left wing',
        'LT 6' => 'Left wing',
        'Usawa' => '',
        'MSB 1' => '',
        'MSB 2' => '',
        'MSB 3' => '',
        'MSB 4' => '',
        'MSB 5' => '',
        'MSB 6' => '',
        'MSB 7' => '',
        'MSB 8' => '',
        'MSB 9' => '',
        'MSB 10' => '',
        'MSB 11' => '',
        'MSB 12' => '',
        'MSB 13' => '',
        'MSB 14' => '',
        'MSB Seminar' => '',
        'Chela Lab' => '',
        'Seminar Room (STC)' => '',
        'iLab Kifaru' => '',
        'Basement Classroom (LIB 1)' => '',
        'Seminar Room (LIB)' => '',
        'RM 1' => 'Right Wing',
        'RM 2' => 'Right Wing',
        'RM 3' => 'Right Wing',
        'RM 4' => 'Right Wing',
        'RM 5' => 'Right Wing',
        'RM 6' => 'Right Wing',
        'RM 7' => 'Right Wing',
        'RM 8' => 'Right Wing',
        'RM 9' => 'Right Wing',
        'RM 10' => 'Right Wing',
        'Suswa Lab' => 'Right Wing',
        'Masinga Lab' => 'Right Wing',
        'Elgon Lab' => 'Left Wing',
        'Kindaruma Lab' => 'Right Wing',
        'Longonot Lab' => 'Right Wing',
        'Aberdare Lab' => 'Left Wing',
        'Language Lab' => 'Right Wing',
        'RM B' => 'Left Wing',
        'Kitchen 5' => 'Left Wing',
        'SBS 1' => '',
        'SBS 2' => '',
        'SLS Shaba' => '',
        'SLS Zumaridi' => '',
        'The Forge 1' => '',
        'The Forge 2' => '',
        'Electronic & Machine Labs' => '',
        'Chemistry Lab' => '',
        'Physics Lab' => '',
    ];

    /**
     * Floors on the building
     */
    private function floorFromName(string $name): int
    {
        if (preg_match('/\bB-/i', $name)) return -1;          // basement
        if (preg_match('/\bGF-/i', $name)) return 0;          // ground floor
        if (preg_match('/\bF(\d+)-/i', $name, $m)) return (int) $m[1];
        return match ($name) {
            'LT 1' => 0,
            'LT 2' => 0,
            'LT 3' => 1,
            'LT 4' => 1,
            'LT 5' => 2,
            'LT 6' => 2,
            'Usawa' => '0',
            'MSB 1' => '0',
            'MSB 2' => '0',
            'MSB 3' => '1',
            'MSB 4' => '1',
            'MSB 5' => '1',
            'MSB 6' => '1',
            'MSB 7' => '2',
            'MSB 8' => '2',
            'MSB 9' => '2',
            'MSB 10' => '2',
            'MSB 11' => '3',
            'MSB 12' => '3',
            'MSB 13' => '3',
            'MSB 14' => '3',
            'MSB Seminar' => '0',
            'Chela Lab' => '2',
            'Seminar Room (STC)' => '3',
            'iLab Kifaru' => '4',
            'Basement Classroom (LIB 1)' => '-1',
            'Seminar Room (LIB)' => '0',
            'RM 1' => '-1',
            'RM 2' => '-1',
            'RM 3' => '-1',
            'RM 4' => '0',
            'RM 5' => '0',
            'RM 6' => '0',
            'RM 7' => '1',
            'RM 8' => '1',
            'RM 9' => '1',
            'RM 10' => '1',
            'Suswa Lab' => '2',
            'Masinga Lab' => '2',
            'Elgon Lab' => '1',
            'Kindaruma Lab' => '2',
            'Longonot Lab' => '1',
            'Aberdare Lab' => '1',
            'Language Lab' => '2',
            'RM B' => '1',
            'Kitchen 5' => '0',
            'SBS 1' => '0',
            'SBS 2' => '0',
            'SLS Shaba' => '-1',
            'SLS Zumaridi' => '-1',
            'The Forge 1' => '1',
            'The Forge 2' => '2',
            'Electronic & Machine Labs' => '0',
            'Chemistry Lab' => '1',
            'Physics Lab' => '1',
            default => 0,
        };
        return 0;                                             // special rooms like "Usawa"
    }

    /**
     * Descriptions of the building
     */
    private array $roomDescriptors = [
        'LT 1' => '',
        'LT 2' => '',
        'LT 3' => '',
        'LT 4' => '',
        'LT 5' => '',
        'LT 6' => '',
        'Usawa' => '',
        'MSB 1' => 'Ground Floor of MSB',
        'MSB 2' => 'Ground Floor of MSB',
        'MSB 3' => 'First Floor of MSB',
        'MSB 4' => 'First Floor of MSB',
        'MSB 5' => 'First Floor of MSB',
        'MSB 6' => 'First of MSB',
        'MSB 7' => 'Second Floor of MSB',
        'MSB 8' => 'Second Floor of MSB',
        'MSB 9' => 'Second Floor of MSB',
        'MSB 10' => 'Second Floor of MSB',
        'MSB 11' => 'Third Floor of MSB',
        'MSB 12' => 'Third Floor of MSB',
        'MSB 13' => 'Third Floor of MSB',
        'MSB 14' => 'Third Floor of MSB',
        'MSB Seminar' => '',
        'Chela Lab' => 'Second Floor in STC',
        'Seminar Room (STC)' => 'Third Floor in STC',
        'iLab Kifaru' => 'Fourth Floor in STC inside iLab',
        'Basement Classroom (LIB 1)' => 'Basement of the Main Library',
        'Seminar Room (LIB)' => 'Just Outside the main Library',
        'RM 1' => 'Basement of the Right Wing',
        'RM 2' => 'Basement of the Right Wing',
        'RM 3' => 'Basement of the Right Wing',
        'RM 4' => 'First Floor of the Right Wing',
        'RM 5' => 'First Floor of the Right Wing',
        'RM 6' => 'First Floor of the Right Wing',
        'RM 7' => 'Second Floor of the Right Wing',
        'RM 8' => 'Second Floor of the Right Wing',
        'RM 9' => 'Second Floor of the Right Wing',
        'RM 10' => 'Second Floor of the Right Wing',
        'Suswa Lab' => 'Middle Lab, close to Masinga',
        'Masinga Lab' => 'Furthest from the main staircase',
        'Elgon Lab' => 'To the Right of the SCES offices',
        'Kindaruma Lab' => 'Closes to the main staircase',
        'Longonot Lab' => 'To the left of the main staircase',
        'Aberdare Lab' => 'Inside the area with the SCES offices',
        'Language Lab' => 'Middle Lab, close to Suswa',
        'RM B' => 'To the Right of the SCES offices, close to Elgon Lab',
        'Kitchen 5' => 'Inside the Cafeteria in Phase 1',
        'SBS 1' => 'At the back of the SBS building',
        'SBS 2' => 'At the back of the SBS building',
        'SLS Shaba' => 'Basement of Oval Building',
        'SLS Zumaridi' => 'Basement of Oval Building',
        'The Forge 1' => 'At the Forge Building',
        'The Forge 2' => 'At the Forge Building',
        'Electronic & Machine Labs' => 'At the Engineering Labs',
        'Chemistry Lab' => 'At the Engineering Labs',
        'Physics Lab' => 'At the Engineering Labs',
    ];
}