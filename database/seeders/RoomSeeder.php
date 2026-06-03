<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove everything from the Room Table before starting
        Room::truncate();

        // ==========================================
        // 1. SIR THOMAS MOORE BUILDING (STMB) - ID: 1
        // ==========================================
        $stmbRooms = [
            // Basement
            ['name' => 'STMB B-01', 'capacity' => 80],
            ['name' => 'STMB B-02', 'capacity' => 80],
            ['name' => 'STMB B-03', 'capacity' => 80],
            ['name' => 'STMB B-04', 'capacity' => 80],// The room with the fancy chairs, check it

            // Ground Floor
            ['name' => 'STMB GF-01', 'capacity' => 80],
            ['name' => 'STMB GF-02', 'capacity' => 80],

            // First Floor
            ['name' => 'STMB F1-01', 'capacity' => 80],// The room with the Microsoft Logo
            ['name' => 'STMB F1-02', 'capacity' => 80],
            ['name' => 'STMB F1-03', 'capacity' => 20],
            ['name' => 'STMB F1-04', 'capacity' => 80],
            ['name' => 'STMB F1-05', 'capacity' => 90],

            // Second Floor
            ['name' => 'STMB F2-01', 'capacity' => 80],
            ['name' => 'STMB F2-02', 'capacity' => 80],
            ['name' => 'STMB F2-03', 'capacity' => 50],
            ['name' => 'STMB F2-04', 'capacity' => 80],
            ['name' => 'STMB F2-05', 'capacity' => 80],

            // Fifth Floor
            ['name' => 'STMB F5-01', 'capacity' => 80],
            ['name' => 'STMB F5-02', 'capacity' => 80],
            ['name' => 'STMB F5-03', 'capacity' => 56],
            ['name' => 'STMB F5-04', 'capacity' => 80],
            ['name' => 'STMB F5-05', 'capacity' => 80],

            // Special Rooms
            ['name' => 'Usawa', 'capacity' => 8],
        ];

        foreach ($stmbRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 1;
            $room->save();
        }


        // ==========================================
        // 2. MANAGEMENT SCIENCES BUILDING (MSB) - ID: 2
        // ==========================================
        $msbRooms = [
            ['name' => 'MSB 1', 'capacity' => 164],
            ['name' => 'MSB 2', 'capacity' => 150],
            ['name' => 'MSB 3', 'capacity' => 94],
            ['name' => 'MSB 4', 'capacity' => 108],
            ['name' => 'MSB 5', 'capacity' => 112],
            ['name' => 'MSB 6', 'capacity' => 112],
            ['name' => 'MSB 7', 'capacity' => 108],
            ['name' => 'MSB 8', 'capacity' => 112],
            ['name' => 'MSB 9', 'capacity' => 112],
            ['name' => 'MSB 10', 'capacity' => 108],
            ['name' => 'MSB 11', 'capacity' => 114],
            ['name' => 'MSB 12', 'capacity' => 96],
            ['name' => 'MSB 13', 'capacity' => 110],
            ['name' => 'MSB 14', 'capacity' => 112],
            ['name' => 'MSB Seminar', 'capacity' => 18], // New Room
        ];

        foreach ($msbRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 2;
            $room->save();
        }


        // ==========================================
        // 3. STUDENT CENTER (STC) - ID: 3
        // ==========================================
        $stcRooms = [
            ['name' => 'ChelaLab', 'capacity' => 42],
            ['name' => 'Seminar Room', 'capacity' => 34], // New Room
            ['name' => 'iLab Kifaru', 'capacity' => 67],
        ];

        foreach ($stcRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 3;
            $room->save();
        }


        // ==========================================
        // 4. UNIVERSITY LIBRARY - ID: 4
        // ==========================================
        $libRooms = [
            ['name' => 'Basement Classroom', 'capacity' => 120],
            ['name' => 'Seminar Room', 'capacity' => 24], // New Room
        ];

        foreach ($libRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 4;
            $room->save();
        }


        // ==========================================
        // 5. CENTRAL BUILDING (CB) - ID: 5
        // ==========================================
        $cbRooms = [
            // Lecture Theatres
            ['name' => 'LT 1', 'capacity' => 150],
            ['name' => 'LT 2', 'capacity' => 155],
            ['name' => 'LT 3', 'capacity' => 150],
            ['name' => 'LT 4', 'capacity' => 155],
            ['name' => 'LT 5', 'capacity' => 168],
            ['name' => 'LT 6', 'capacity' => 150],
            
            // Classrooms
            ['name' => 'RM 1', 'capacity' => 52],
            ['name' => 'RM 2', 'capacity' => 66],
            ['name' => 'RM 3', 'capacity' => 56],
            ['name' => 'RM 4', 'capacity' => 46],
            ['name' => 'RM 5', 'capacity' => 40],
            ['name' => 'RM 6', 'capacity' => 94],
            ['name' => 'RM 7', 'capacity' => 48],
            ['name' => 'RM 8', 'capacity' => 62],
            ['name' => 'RM 9', 'capacity' => 112],
            ['name' => 'RM 10', 'capacity' => 56],
            
            // Labs & Special Rooms
            ['name' => 'SuswaLab', 'capacity' => 65],
            ['name' => 'MasingaLab', 'capacity' => 63],
            ['name' => 'ElgonLab', 'capacity' => 35],
            ['name' => 'KindarumaLab', 'capacity' => 60],
            ['name' => 'LongonotLab', 'capacity' => 37],
            ['name' => 'AberdareLab', 'capacity' => 47],
            ['name' => 'LanguageLab', 'capacity' => 20],
            ['name' => 'RM B', 'capacity' => 48],
            ['name' => 'Kitchen 5', 'capacity' => 50],
            ['name' => 'RM B', 'capacity' => 48],
        ];

        foreach ($cbRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 5;
            $room->save();
        }


        // ==========================================
        // 6. STRATHMORE BUSINESS SCHOOL (SBS) - ID: 6
        // ==========================================
        $sbsRooms = [
            ['name' => 'SBS 1', 'capacity' => 80],
            ['name' => 'SBS 2', 'capacity' => 80],
        ];

        foreach ($sbsRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 6;
            $room->save();
        }


        // ==========================================
        // 7. OVAL BUILDING (OB) - ID: 7
        // ==========================================
        $ovalRooms = [
            ['name' => 'SLS Shaba', 'capacity' => 80],
            ['name' => 'SLS Zumaridi', 'capacity' => 80],
        ];

        foreach ($ovalRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 7;
            $room->save();
        }


        // ==========================================
        // 8. STRATHMORE ENGINEERING LABS (SERC) - ID: 8
        // ==========================================
        $sercRooms = [
            ['name' => 'The Forge 1', 'capacity' => 80],
            ['name' => 'The Forge 2', 'capacity' => 100],
            ['name' => 'Electronic & Machine Labs', 'capacity' => 20],
            ['name' => 'Chemistry lab', 'capacity' => 45],
            ['name' => 'Physics lab', 'capacity' => 45],
        ];

        foreach ($sercRooms as $roomData) {
            $room = new Room();
            $room->room_name = $roomData['name'];
            $room->capacity = $roomData['capacity'];
            $room->building_id = 8;
            $room->save();
        }
    }
}