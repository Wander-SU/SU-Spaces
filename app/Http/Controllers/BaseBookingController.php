<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorebaseBookingRequest;
use App\Http\Requests\UpdatebaseBookingRequest;
use App\Models\BaseBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class BaseBookingController extends Controller
{

    /**
     * Global Variables
     */
    /**
     * All the rooms are within this public array
     * It is not held within a function to reduce redundant data writes
     */
    /**
     * Names here are the ones shown in the raw csv
     * Electronics lab (1 occurrence) and Electronic & Machine Labs are 1 room
     */
    public static array $roomNameToId = [
        "B-01" => 1, "B-02" => 2, "B-03" => 3, "B-04" => 4,
        "GF-01" => 5, "GF-02" => 6,
        "F1-01" => 7, "F1-02" => 8, "F1-03" => 9, "F1-04" => 10, "F1-05" => 11,
        "F2-01" => 12, "F2-02" => 13, "F2-03" => 14, "F2-04" => 15, "F2-05" => 16,
        "F5-01" => 17, "F5-02" => 18, "F5-03" => 19, "F5-04" => 20, "F5-05" => 21,
        "Usawa" => 22,
        "MSB 1" => 23, "MSB 2" => 24, "MSB 3" => 25, "MSB 4" => 26, "MSB 5" => 27,
        "MSB 6" => 28, "MSB 7" => 29, "MSB 8" => 30, "MSB 9" => 31, "MSB 10" => 32,
        "MSB 11" => 33, "MSB 12" => 34, "MSB 13" => 35, "MSB 14" => 36, "MSB Seminar" => 37,
        "Chela Lab" => 38, "SEM RM" => 39, "Kifaru" => 40,
        "Lib 1" => 41, "SEM" => 42,
        "LT 1" => 43, "LT 2" => 44, "LT 3" => 45, "LT 4" => 46, "LT 5" => 47, "LT 6" => 48,
        "RM 1" => 49, "RM 2" => 50, "RM 3" => 51, "RM 4" => 52, "RM 5" => 53,
        "RM 6" => 54, "RM 7" => 55, "RM 8" => 56, "RM 9" => 57, "RM 10" => 58,
        "Suswa Lab" => 59, "Masinga Lab" => 60, "Elgon Lab" => 61, "Kindaruma" => 62,
        "Longonot" => 63, "Aberdare" => 64, "Lang Lab" => 65,
        "RM B" => 66, "Kitchen 5" => 67,
        "SBS 1" => 68, "SBS 2" => 69,
        "Shaba" => 70, "Zumaridi" => 71,
        "The Forge 1" => 72, "The Forge 2" => 73,"Electronics lab"=>74,
        "Electronic and machine labs" => 74, "Chemistry lab" => 75, "Physics Lab" => 76,
    ];

    /**
     * Used to Map The start times to the time slots in the table
     */
    public static array $startTimeToId = [
        "00:00:00" => 1,
        "00:15:00" => 2,
        "00:30:00" => 3,
        "00:45:00" => 4,
        "01:00:00" => 5,
        "01:15:00" => 6,
        "01:30:00" => 7,
        "01:45:00" => 8,
        "02:00:00" => 9,
        "02:15:00" => 10,
        "02:30:00" => 11,
        "02:45:00" => 12,
        "03:00:00" => 13,
        "03:15:00" => 14,
        "03:30:00" => 15,
        "03:45:00" => 16,
        "04:00:00" => 17,
        "04:15:00" => 18,
        "04:30:00" => 19,
        "04:45:00" => 20,
        "05:00:00" => 21,
        "05:15:00" => 22,
        "05:30:00" => 23,
        "05:45:00" => 24,
        "06:00:00" => 25,
        "06:15:00" => 26,
        "06:30:00" => 27,
        "06:45:00" => 28,
        "07:00:00" => 29,
        "07:15:00" => 30,
        "07:30:00" => 31,
        "07:45:00" => 32,
        "08:00:00" => 33,
        "08:15:00" => 34,
        "08:30:00" => 35,
        "08:45:00" => 36,
        "09:00:00" => 37,
        "09:15:00" => 38,
        "09:30:00" => 39,
        "09:45:00" => 40,
        "10:00:00" => 41,
        "10:15:00" => 42,
        "10:30:00" => 43,
        "10:45:00" => 44,
        "11:00:00" => 45,
        "11:15:00" => 46,
        "11:30:00" => 47,
        "11:45:00" => 48,
        "12:00:00" => 49,
        "12:15:00" => 50,
        "12:30:00" => 51,
        "12:45:00" => 52,
        "13:00:00" => 53,
        "13:15:00" => 54,
        "13:30:00" => 55,
        "13:45:00" => 56,
        "14:00:00" => 57,
        "14:15:00" => 58,
        "14:30:00" => 59,
        "14:45:00" => 60,
        "15:00:00" => 61,
        "15:15:00" => 62,
        "15:30:00" => 63,
        "15:45:00" => 64,
        "16:00:00" => 65,
        "16:15:00" => 66,
        "16:30:00" => 67,
        "16:45:00" => 68,
        "17:00:00" => 69,
        "17:15:00" => 70,
        "17:30:00" => 71,
        "17:45:00" => 72,
        "18:00:00" => 73,
        "18:15:00" => 74,
        "18:30:00" => 75,
        "18:45:00" => 76,
        "19:00:00" => 77,
        "19:15:00" => 78,
        "19:30:00" => 79,
        "19:45:00" => 80,
        "20:00:00" => 81,
        "20:15:00" => 82,
        "20:30:00" => 83,
        "20:45:00" => 84,
        "21:00:00" => 85,
        "21:15:00" => 86,
        "21:30:00" => 87,
        "21:45:00" => 88,
        "22:00:00" => 89,
        "22:15:00" => 90,
        "22:30:00" => 91,
        "22:45:00" => 92,
        "23:00:00" => 93,
        "23:15:00" => 94,
        "23:30:00" => 95,
        "23:45:00" => 96,
    ];

    public static array $endTimeToId = [
        "00:15:00" => 1,
        "00:30:00" => 2,
        "00:45:00" => 3,
        "01:00:00" => 4,
        "01:15:00" => 5,
        "01:30:00" => 6,
        "01:45:00" => 7,
        "02:00:00" => 8,
        "02:15:00" => 9,
        "02:30:00" => 10,
        "02:45:00" => 11,
        "03:00:00" => 12,
        "03:15:00" => 13,
        "03:30:00" => 14,
        "03:45:00" => 15,
        "04:00:00" => 16,
        "04:15:00" => 17,
        "04:30:00" => 18,
        "04:45:00" => 19,
        "05:00:00" => 20,
        "05:15:00" => 21,
        "05:30:00" => 22,
        "05:45:00" => 23,
        "06:00:00" => 24,
        "06:15:00" => 25,
        "06:30:00" => 26,
        "06:45:00" => 27,
        "07:00:00" => 28,
        "07:15:00" => 29,
        "07:30:00" => 30,
        "07:45:00" => 31,
        "08:00:00" => 32,
        "08:15:00" => 33,
        "08:30:00" => 34,
        "08:45:00" => 35,
        "09:00:00" => 36,
        "09:15:00" => 37,
        "09:30:00" => 38,
        "09:45:00" => 39,
        "10:00:00" => 40,
        "10:15:00" => 41,
        "10:30:00" => 42,
        "10:45:00" => 43,
        "11:00:00" => 44,
        "11:15:00" => 45,
        "11:30:00" => 46,
        "11:45:00" => 47,
        "12:00:00" => 48,
        "12:15:00" => 49,
        "12:30:00" => 50,
        "12:45:00" => 51,
        "13:00:00" => 52,
        "13:15:00" => 53,
        "13:30:00" => 54,
        "13:45:00" => 55,
        "14:00:00" => 56,
        "14:15:00" => 57,
        "14:30:00" => 58,
        "14:45:00" => 59,
        "15:00:00" => 60,
        "15:15:00" => 61,
        "15:30:00" => 62,
        "15:45:00" => 63,
        "16:00:00" => 64,
        "16:15:00" => 65,
        "16:30:00" => 66,
        "16:45:00" => 67,
        "17:00:00" => 68,
        "17:15:00" => 69,
        "17:30:00" => 70,
        "17:45:00" => 71,
        "18:00:00" => 72,
        "18:15:00" => 73,
        "18:30:00" => 74,
        "18:45:00" => 75,
        "19:00:00" => 76,
        "19:15:00" => 77,
        "19:30:00" => 78,
        "19:45:00" => 79,
        "20:00:00" => 80,
        "20:15:00" => 81,
        "20:30:00" => 82,
        "20:45:00" => 83,
        "21:00:00" => 84,
        "21:15:00" => 85,
        "21:30:00" => 86,
        "21:45:00" => 87,
        "22:00:00" => 88,
        "22:15:00" => 89,
        "22:30:00" => 90,
        "22:45:00" => 91,
        "23:00:00" => 92,
        "23:15:00" => 93,
        "23:30:00" => 94,
        "23:45:00" => 95,
        "00:00:00" => 96,
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('updateTimetable.view');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorebaseBookingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BaseBooking $baseBooking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BaseBooking $baseBooking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatebaseBookingRequest $request, BaseBooking $baseBooking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BaseBooking $baseBooking)
    {
        //
    }

    /**
     * Room Mapping functionality
     */
    public function mapRoom(String $roomName)
    {
        // Map the room name to an Id and return null when unmapped.
        $roomId = self::$roomNameToId[$roomName];
        return $roomId;
    }

    /**
     * Mapping the start times to the correct fields
     */
    public static function mapStartTime(String $startTime)
    {
        $startTimeId =  self::$startTimeToId[$startTime];
        return $startTimeId;
    }

    /**
     * Mapping the end times to the correct fields
     */
    public static function mapEndTime(String $endTime)
    {
        $endTimeId =  self::$endTimeToId[$endTime];
        return $endTimeId;
    }

    /**
     * Update the timetable fully
     */
    public function updateFull()
    {
        // Get the file
        $filename = "BaseTimetableData.csv";

        if (!Storage::disk('local')->exists($filename)) {
            return redirect()->route('baseBookings.index')->with('error', 'Failed to open the local file stream.');
        }

        // Open a read stream
        $handle = Storage::disk('local')->readStream($filename);

        // Error handling on $handle
        if ($handle === false) {
            return redirect()->route('baseBookings.index')->with('error', 'Failed to open the local file stream.');
        }

        // Skip the header row
        $header = fgetcsv($handle,null,',');

        // Variables to help understand where we are at in cycle of importing
        $importedRows=0;
        $dataToInsert = [];

        // Delete the rows from the table first
        BaseBooking::truncate();

        // Read until false
        while(($row = fgetcsv($handle,null,',')) !== FALSE){
            // Assignment
            $created_at = now();
            $updated_at = now();
            $RowNumber = $row[0];
            $course = $row[1];
            $semester = $row[2];
            $academic_year = $row[3];
            $academic_session = $row[4];
            $subject = $row[5];
            $course_number = $row[6];
            $unit_name = $row[7];
            $lecturer = $row[8];
            $lesson_date = $row[9];
            $lesson_day = $row[10];
            $class_group = $row[11];
            $lesson_start = $row[12];
            $lesson_end = $row[13];
            $building = $row[14];
            $venue = $row[15];
            $class_size = $row[16];
            $room_capacity = $row[17];

            if($RowNumber != null){
                /**
                 * Clean Up
                 * Make the hours and minutes to become a more standard format (HH:MM:SS)
                 * Map the rooms:
                    * Only map if not null and not VB.
                    * 
                */ 
                // Lesson_start format
                if(!empty($lesson_start)){
                    try{
                        $lesson_start =Carbon::createFromFormat("H:i",trim($lesson_start))->format('H:i:s');
                        $lesson_start=self::mapEndTime($lesson_start);
                    }catch(\Throwable $e){
                        $lesson_start =null;
                    }
                }else{
                    $lesson_start =null;
                }

                // Lesson_end format
                if(!empty($lesson_end)){
                    try{
                        $lesson_end =Carbon::createFromFormat("H:i",trim($lesson_end))->format('H:i:s');
                        $lesson_end=self::mapEndTime($lesson_end);
                    }catch(\Throwable $e){
                        $lesson_end =null;
                    }
                }else{
                    $lesson_end =null;
                }

                // Map the rooms
                $room_id = null;
                $virtualLesson = preg_match('/Virtual Class/',$venue) ? true : false;
                if(!empty($venue) && $virtualLesson==false){
                    $room_id = self::mapRoom(trim($venue));
                }
                
                if($room_id!=null){
                    // Map database columns to CSV columns 
                    $dataToInsert[] = [
                        'created_at'=>$created_at,
                        'updated_at'=>$updated_at,
                        'course' => trim($course),
                        'semester' => trim($semester),
                        'academic_year' => trim($academic_year),
                        'academic_session' => trim($academic_session),
                        'subject' => trim($subject),
                        'course_number' => trim($course_number),
                        'unit_name' => trim($unit_name),
                        'lesson_day' => trim($lesson_day),
                        'start_time_id' => $lesson_start,
                        'end_time_id' => $lesson_end,
                        'room_id' => trim($room_id)
                    ];
                }

                // Insert if enough data 500 rows reached
                if(count($dataToInsert) ===50){
                    BaseBooking::insert($dataToInsert);
                    $importedRows += count($dataToInsert);
                    $dataToInsert = [];
                }
            }
        }

        // Insert leftover rows
        if(count($dataToInsert) !==0){
                BaseBooking::insert($dataToInsert);
                $importedRows += count($dataToInsert);
        }
        
        // Close the stream
        fclose($handle);

        return redirect()->route('baseBookings.index')->with("info","You inserted ".$importedRows." rows");
    }
}
