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
    public array $roomNameToId = [
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
        "SBS 1" => 69, "SBS 2" => 70,
        "Shaba" => 71, "Zumaridi" => 72,
        "The Forge 1" => 73, "The Forge 2" => 74,"Electronics lab"=>75,
        "Electronic and machine labs" => 75, "Chemistry lab" => 76, "Physics Lab" => 77,
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
        // Map the room name to an Id and return it
        $roomId = $this->roomNameToId[$roomName];
        return $roomId;        
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
                        $lesson_start=Carbon::createFromFormat("H:i",trim($lesson_start))->format('H:i:s');
                    }catch(\Throwable $e){
                        $lesson_start=null;
                    }
                }else{
                    $lesson_start=null;
                }

                // Lesson_end format
                if(!empty($lesson_end)){
                    try{
                        $lesson_end =Carbon::createFromFormat("H:i",trim($lesson_end))->format('H:i:s');
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
                    $room_id = $this->mapRoom(trim($venue));
                }
                
                if($room_id!=null){
                    // Map database columns to CSV columns 
                    $dataToInsert[] = [
                        'course' => trim($course),
                        'semester' => trim($semester),
                        'academic_year' => trim($academic_year),
                        'academic_session' => trim($academic_session),
                        'subject' => trim($subject),
                        'course_number' => trim($course_number),
                        'unit_name' => trim($unit_name),
                        'lesson_day' => trim($lesson_day),
                        'start_time' => trim($lesson_start),
                        'end_time' => trim($lesson_end),
                        'room_id' => trim($room_id)
                    ];
                }

                // Insert if enough data 500 rows reached
                if(count($dataToInsert) ===50){
                    baseBooking::insert($dataToInsert);
                    $importedRows += count($dataToInsert);
                    $dataToInsert = [];
                }
            }
        }

        // Insert leftover rows
        if(count($dataToInsert) !==0){
                baseBooking::insert($dataToInsert);
                $importedRows += count($dataToInsert);
        }
        
        // Close the stream
        fclose($handle);

        return redirect()->route('baseBookings.index')->with("success","You inserted ".$importedRows." rows");
    }
}
