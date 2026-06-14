<?php

use App\Http\Controllers\BaseBookingController;
use App\Mail\BookingVoided;
use App\Models\BaseBooking;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Rules\AlphaSpaces;
use App\Rules\CourseSessionData;
use App\Rules\DateGreaterThanToday;
use App\Rules\DateValid;
use App\Rules\DayOfWeek;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = "bootstrap";
    public $search;
    public $search_date= "";
    public $search_building=1; //Crook Behaviour, this can be done in a much better manner. 
    public $orderDirection1 = "asc";
    public $orderDirection2 = "asc";
    public $orderDirectionTime = "asc";
    public $building_id;
    public $building_name;
    public $start_time;
    public $end_time;
    public $room_name;
    public $book_date;
    public $number_occupants;
    public $book_reason="Individual Study";
    public $start_time_id;
    public $end_time_id;
    public $room_id;
    public $orderField;
    public $showForm = false;
    public $user_id=0;  //Test Purposes
    public $lecBooked=0;
    public $isPrivilegedBook;
    public $vacancies;
    public $room_capacity;
    public $initial_start_time_id;
    public $initial_end_time_id;
    public $now;

    public function render()
    {
      // Get Room Details
      $rooms = Room::all();

      // Get TimeSlot Details
      $timeSlots = TimeSlot::all();

      // Get buildings
      $buildings = Building::all();

      // Get the day of the week
      $dayOfWeek = $this->getDayOfWeek($this->search_date);

      // Get the date and time rn
      $this->now = time();
      
      // Get Rooms Available;
      $items = Room::query()
      ->join('buildings','buildings.id','=','rooms.building_id')
      ->crossjoin(DB::raw('(SELECT "Monday" as lesson_day union all
        select "Tuesday" union all
        select "Wednesday" union all
        select "Thursday" union all
        select "Friday" union all
        select "Saturday")as d'))
      ->crossjoin('time_slots as ts')
      ->leftjoin('base_bookings as b',function($join){
        $join->whereRaw('ts.id between b.start_time_id and b.end_time_id')
        ->whereColumn('b.room_id','rooms.id')
        ->whereColumn('b.lesson_day','d.lesson_day');
      })
      ->whereNull('b.id')
      ->where('ts.start_time','>=','07:00:00')
      ->where('ts.end_time','<=','21:00:00')
      ->where('ts.end_time','!=','00:00:00')
      ->where('rooms.building_id',$this->search_building)
      ->where('rooms.room_name','like',"%$this->search%")
      ->where('d.lesson_day','like',"%$dayOfWeek%")
      ->select([
          'rooms.id as room_id',
          'rooms.room_name as room_name',
          'buildings.building_name as building_name',
          'buildings.id as building_id',
          'ts.id',
          'ts.start_time as start_time',
          'ts.end_time as end_time',
          'rooms.capacity as capacity',
          'd.lesson_day as lesson_day'
      ])
      ->orderBy('buildings.building_name',$this->orderDirection1)
      ->orderBy('rooms.room_name',$this->orderDirection2)
      ->orderByRaw("FIELD(d.lesson_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
      ->orderBy('ts.start_time',$this->orderDirectionTime)
      ->get();
      
      /**
       * Consolidation Logic
      */
      if($this->orderDirectionTime=="asc")
      {
        for($i=0;$i<count($items);$i++){
          // Get the next time
          $j=$i+1;
          // See if the next time is equal to the current end time and keep checking 
          while($j!=count($items)){
            // Evaluate if the current end time is the same as the next one 
            // Set the current end time to be similar to the next start time
            if(($items[$i]['end_time']==$items[$j]["start_time"])){
              $items[$i]['end_time']=$items[$j]["end_time"];
              $items[$j]['start_time']=null;
            }
            else{
              // Don't have to loop through the entire file
              break;
            }
            $j+=1;
          }
          $i=$j-1;       
        }

        $filtered = $items->filter(fn($items)=>$items["start_time"]!==null)->values();
      }

      if($this->orderDirectionTime=="desc")
      {
        for($i=0;$i<count($items);$i++){
          // Get the next time
          $j=$i+1;
          // See if the next time is equal to the current end time and keep checking 
          while($j!=count($items)){
            // Evaluate if the current end time is the same as the next one 
            // Set the current end time to be similar to the next start time
            if(($items[$i]['start_time']==$items[$j]["end_time"])){
              $items[$i]['start_time']=$items[$j]["start_time"];
              $items[$j]['end_time']=null;
            }
            else{
              // Don't have to loop through the entire file
              break;
            }
            $j+=1;
          }
          $i=$j-1;       
        }

        $filtered = $items->filter(fn($items)=>$items["end_time"]!==null)->values();
      } 

      // Manually Paginate
      $perPage = env('PAGINATION_COUNT',50);
      $page = Paginator::resolveCurrentPage();


      $roomsAvailable = new LengthAwarePaginator(
        $filtered->forPage($page,$perPage),
        $filtered->count(),
        $perPage,
        $page,
        ["path" => Paginator::resolveCurrentPath()]
      );

      return view('components.quick-access.⚡quick-access',compact('roomsAvailable','timeSlots','rooms','buildings'));
    }

    /**
     * Order By Fields
    */
    public function orderBy($field)
    {
        //Update the orderfield
        $this->orderField = $field;

        if($this->orderField=="building_name"){
          if($this->orderDirection1 == "asc"){
            $this->orderDirection1 = "desc";
          }
          else{
            $this->orderDirection1 = "asc";
          }
        }

        if($this->orderField=="room_name"){
          if($this->orderDirection2 == "asc"){
            $this->orderDirection2= "desc";
          }
          else{
            $this->orderDirection2 = "asc";
          }
        }

        if($this->orderField=="start_time_id"){
          if($this->orderDirectionTime == "asc"){
            $this->orderDirectionTime = "desc";
          }
          else{
            $this->orderDirectionTime = "asc";
          }
        }
    }

    /**
     * Validation Rules
    */
    public function rules(){
      return [
        "number_occupants"=>["required"],
        "book_reason"=>["required"],
        "book_date"=>["required",new DateGreaterThanToday()],
        "end_time_id"=>["required"]
      ];
    }


    /**
     * Resetting the search
     */
    public function clearSearch(){
        $this->search = "";
    }

    /**
     * Reset the form
     * Set show form to true
     */
    public function add(){
        $this->reset();
        $this->showForm = true;
    }

    /**
     * Remove the form from view
    */
    public function cancel(){
        $this->reset();
        $this->showForm = false;
    }

    /**
     * Get Time Conflict
     * Check the base boooking table where the room_is is the room_id we are working with
     * Check the lesson day is the day we are working with
     * Check Two things:
          *If the start_time_id on the row is less than the end time of what we have there is a conflict, if something starts earlier than we finish we should not book
          *IF the end_time_id on the row is more than the start time of the booking we should not book, we cannot book before they end their class
    */
    public function hasTimeConflict(){
      return BaseBooking::where('room_id',$this->room_id)
      ->where('lesson_day',date("l",strToTime($this->book_date)))
      ->where(function($q){
        $q->where('start_time_id','<',$this->end_time_id)
        ->where('end_time_id','>',$this->start_time_id);
      })
      ->exists();
    }

    public function backwardsTimeLogic(){
      if($this->end_time_id<$this->start_time_id){
        return true;
      }
    }

    /**
     * Show which data on the form
    */
    public function showBookForm($building_id,$room_id,$start_time,$end_time,$capacity){
      $this->showForm = True;
      $building = Building::findOrFail($building_id);
      $room = Room::findOrFail($room_id);
      $this->building_id = $building->id;
      $this->building_name = $building->building_name;
      $this->room_capacity = $capacity;
      $this->room_id = $room->id;
      $this->room_name = $room->room_name;
      $this->book_date = $this->search_date;
      $this->initial_start_time_id = BaseBookingController::mapStartTime($start_time);
      $this->start_time_id = BaseBookingController::mapStartTime($start_time);
      $this->initial_end_time_id = BaseBookingController::mapEndTime($end_time);
      $this->end_time_id = BaseBookingController::mapEndTime($end_time);
      $this->computeVacancies();
    }

    /**
     * The privileged version of booking
    */
    public function showBookFormPrivileged($building_id,$room_id,$start_time,$end_time,$capacity){
      $this->showForm = True;
      $this->isPrivilegedBook = True;
      $building = Building::findOrFail($building_id);
      $room = Room::findOrFail($room_id);
      $this->building_id = $building->id;
      $this->building_name = $building->building_name;
      $this->room_capacity = $capacity;
      $this->room_id = $room->id;
      $this->room_name = $room->room_name;
      $this->number_occupants = $room->capacity;
      $this->book_date = $this->search_date;
      $this->initial_start_time_id = BaseBookingController::mapStartTime($start_time);
      $this->start_time_id = BaseBookingController::mapStartTime($start_time);
      $this->end_time_id = BaseBookingController::mapEndTime($end_time);
      $this->initial_end_time_id = BaseBookingController::mapEndTime($end_time);
      $this->computeVacancies();
    }

    /**
     * Get the day of the week that it is
     * strToTime -> Gets a timestamp
     * l -> Give the full day format
    */
    public function getDayOfWeek(String $date=null){
      if($date!=null){
        $dayOfWeek = date("l",strToTime($date));
        return $dayOfWeek;
      }
      else{
        return "";
      }
    }

    /**
     * Save a booking to the database
    */
    public function book(){
      // Validate the data before sending it to the database
      $this->validate();
      $status = "Booked";

      // Check all timing conflicts
      // Get if timing conflict
      if($this->hasTimeConflict()){
        $this->addError('time','This room already has a booking that overlaps with the selected time on ' . $this->book_date . '.');
        return;
      }
      
      // Get if wrong End Time Logic
      if($this->backwardsTimeLogic()){
        $this->addError('time','This time is less than the specified start time indicated .');
        return;
      }

      // Format the Start and End Time IDs
      $this->start_time_id = (int)trim($this->start_time_id);
      $this->end_time_id = (int)trim($this->end_time_id);

      // dd($this->start_time_id,$this->end_time_id);
      // dd($this->start_time_id,$this->end_time_id);
      // Create the tuple that is needed
      try{
      $booking = Booking::create([
        "created_at"=>now(),
        "updated_at"=>now(),
        "attendee_count"=>$this->number_occupants,
        "status"=>$status,
        "purpose"=>$this->book_reason,
        "booking_date"=>$this->book_date,
        "room_id"=>$this->room_id,
        "user_id"=>1,
        "start_time_id"=>$this->start_time_id,
        "end_time_id"=>$this->end_time_id,
      ]);
      $booking->save();

      // Show the table
      $this->showForm = false;
      $this->resetExcept(["search_date","search_building","room_name","building_name"]);

      session()->flash("success","Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants");
      }
      catch(\Throwable $e){
        session()->flash('failure',"There was a database error");
      }
    }

    /**
     * Void a booking
    */
    public function void($id){
      try{
        $bookingToBeVoided = Booking::findOrFail($id);
        $bookingToBeVoided->status = "Voided";
        $bookingToBeVoided->save();
        Mail::to($bookingToBeVoided->user->email)->send(new BookingVoided($bookingToBeVoided));
      }catch(\Throwable $e){
        session()->flash('failure',"Could not void the bookings of other students".$e->getMessage());
      }
    }

    /**
     * Privileged Booking to the database
    */
    public function bookPrivileged(){
      // Validate the data before sending it to the database
      $this->validate();
      $status = "Booked";

      // Check all timing conflicts with Base Bookings
      // Get if timing conflict
      if($this->hasTimeConflict()){
        $this->addError('time','This room already has a booking that overlaps with the selected time on ' . $this->book_date . '.');
        return;
      }
      
      // Get if wrong End Time Logic
      if($this->backwardsTimeLogic()){
        $this->addError('time','This time is less than the specified start time indicated .');
        return;
      }

      // Format the Start and End Time IDs
      $this->start_time_id = (int)trim($this->start_time_id);
      $this->end_time_id = (int)trim($this->end_time_id);

      // Create the tuple that is needed
      try{
        // Get the total number of voidable bookings that we should do
        $toVoid = $this->getVoidable($this->start_time_id,$this->end_time_id,$this->room_id,$this->book_date);

        // Void each row of the things to be voided
        if(count($toVoid)>0){
          forEach($toVoid as $voidable){
            // Change this to do it only on Students
            if($voidable["user_id"]!=1){
              $this->addError('lecturer_booked','Could not book this room because a lecturer'.$voidable["user_id"].'has booked this room at'.$voidable["start_time_id"].'to'.$voidable["end_time_id"].'for a'.$voidable["purpose"] );
              return;
            }
          }
          forEach($toVoid as $voidable){
            $this->void($voidable["id"]);
          }
        }

        $booking = Booking::create([
        "created_at"=>now(),
        "updated_at"=>now(),
        "attendee_count"=>$this->number_occupants,
        "status"=>$status,
        "purpose"=>$this->book_reason,
        "booking_date"=>$this->book_date,
        "room_id"=>$this->room_id,
        "user_id"=>1,
        "start_time_id"=>$this->start_time_id,
        "end_time_id"=>$this->end_time_id,
      ]);
      $booking->save();

      // Show the table
      $this->showForm = false;
      $this->resetExcept(["search_date","search_building","room_name","building_name"]);

      session()->flash("success","Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants");
      }
      catch(\Throwable $e){
        session()->flash('failure',"There was a database error");
      }
    }

    /**
     * 
    */
    public function getVoidable(int $start_time_id,int $end_time_id, int $room_id, string $prospected_date){

      $utilised = Booking::query()
      ->where('start_time_id','<=',"$end_time_id")
      ->where('end_time_id','>=',"$start_time_id")
      ->where('room_id',$room_id)
      ->where('booking_date',"$prospected_date")
      ->get();

      return $utilised;
    }
    /**
     * Get the number of available spaces for that room on that particular day
     * Get the start time, get the end time, get the room_id and get the prospected date
    */
    public function roomUtilisation(string $start_time,string $end_time, int $room_id, string $prospected_date){

      $start_time_id = BaseBookingController::mapStartTime($start_time);
      $end_time_id = BaseBookingController::mapEndTime($end_time);

      $utilised = Booking::query()
      ->where('start_time_id','<=',"$end_time_id")
      ->where('end_time_id','>=',"$start_time_id")
      ->where('room_id',"$room_id")
      ->where('booking_date',"$prospected_date")
      ->sum('attendee_count');

      return $utilised;
    }

    public function roomUtilisationUsingIds(int $start_time_id,int $end_time_id, int $room_id, string $prospected_date){

      $utilised = Booking::query()
      ->where('start_time_id','<=',"$end_time_id")
      ->where('end_time_id','>=',"$start_time_id")
      ->where('room_id',"$room_id")
      ->whereNot('status',"Voided")
      ->where('booking_date',"$prospected_date")
      ->sum('attendee_count');

      return $utilised;
    }

    /**
     * Function to get the vacancies
    */
    public function computeVacancies(){
      $this->vacancies = $this->room_capacity - $this->roomUtilisationUsingIds($this->start_time_id,$this->end_time_id,$this->room_id,$this->search_date);
    }

    /**
     * Compute the vacancies on every update of start_time_id or end_time_id
    */
    public function updatedStartTimeId(){
      $this->computeVacancies();
    }

    public function updatedEndTimeId(){
      $this->computeVacancies();
    }

}
?>

<div class="col-md-12">
    {{-- Root Element: Livewire views need this --}}

    {{-- Show the messages --}}
    @if (session()->has('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session()->has('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    
    {{-- Form for Booking--}}
    @if($showForm)
      <div class="col-md-4 z-3 position-absolute top-0 end-0 rounded-3 card card-info card-outline mb-4">
          <div class="card-header">
              <div class="card-title">Book Room</div>
          </div>
          <form wire:submit="{{$this->isPrivilegedBook ? "bookPrivileged" : "book"}}">
              @csrf
              <div class="card-body">
                <div class="row">
                  {{-- Building --}}
                  <div class="col-md-12 mb-3">
                    <label for="building_name" class="form-label">Building</label>
                    <input wire:model="building_name" type="text" disabled name="building_name" class="form-control @error('building_name') is-invalid @enderror" value="{{old('building_name')}}">
                    @error('building_name')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Room --}}
                  <div class="col-md-6 mb-3">
                    <label for="room_name" class="form-label">Room</label>
                    <input wire:model="room_name" type="text" name="room_name" disabled class="form-control @error('room_name') is-invalid @enderror" value="{{old('room_name')}}">
                    @error('room_name')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Date --}}
                  <div class="col-md-6 mb-3">
                      <label for="date" class="form-label">Book Date</label>
                      <input required wire:model="book_date" type="date" disabled name="book_date" class="form-control @error('room_id') is-invalid @enderror" value="{{old('book_date')}}">
                    @error('book_date')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Time --}}
                  <div class="col-md-12">
                    {{-- Start Time --}}
                    <div class="col-md-4 d-inline-block mb-3 me-3">
                      <label for="start_time_id" class="form-label">Start Time</label>
                      <select required wire:model.live.debounce.500ms="start_time_id" name="start_time_id" class="form-control @error('start_time_id') is-invalid @enderror" value="{{old('start_time_id')}}">
                        @foreach ($timeSlots as $timeSlot)
                          @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00" && $timeSlot->id>=$this->initial_start_time_id && $timeSlot->id<=$this->initial_end_time_id)
                            <option value="{{ $timeSlot->id }}">
                              {{ $timeSlot->start_time }}
                            </option>
                          @endif
                        @endforeach
                      </select>
                    </div>

                    {{-- End Time --}}
                    <div class="col-md-4 d-inline-block mb-3 me-3">
                      <label for="end_time_id" class="form-label">End Time</label>
                      <select required wire:model.live.debounce.500ms="end_time_id" name="end_time_id" class="form-control @error('end_time_id') is-invalid @enderror" value="{{old('end_time_id')}}">
                        @foreach ( $timeSlots as $timeSlot )
                          @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00" && $timeSlot->id>=$this->initial_start_time_id && $timeSlot->id<=$this->initial_end_time_id)
                            <option value="{{ $timeSlot->id }}">
                              {{ $timeSlot->end_time }}
                            </option>
                          @endif
                        @endforeach
                      </select>
                    </div>

                    {{-- Vacancies Based on Time Slot --}}
                    <div class="col-md-2 d-inline-block mb-3">
                      <label for="vacancies" class="form-label">Vacancies</label>
                      <input required disabled wire:model="vacancies" name="vacancies" class="form-control @error('vacancies') is-invalid @enderror" >
                    </div>
                    @error('time')
                        <div class="invalid-feedback">
                          {{ $message }}
                        </div>
                    @enderror
                    @error('vacancies')
                        <div class="invalid-feedback">
                          {{ $message }}
                        </div>
                    @enderror
                  </div>                  

                  {{-- Occupants --}}
                  <div class="col-md-6 mb-3">
                    <label for="number_occupants" class="form-label">Number of Occupants</label>
                    <input required wire:model.live.debounce.500ms="number_occupants"
                     @if($this->user_id!=1) disabled @endif
                     type="number" min=1 name="number_occupants" class="form-control @error('number_occupants') is-invalid @enderror" value="{{old('number_occupants')}}">
                    @error('number_occupants')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Reason For Booking --}}
                  <div class="col-md-6 mb-3">
                    <label for="book_reason" class="form-label">Reason For Booking</label>
                    <select required wire:model="book_reason" type="text" name="book_reason" class="form-control @error('book_reason') is-invalid @enderror" value="{{old('book_reason')}}">
                      @if($this->user_id==1)
                        <option value="Individual Study">Individual Study</option>
                        <option value="Group Study">Group Study</option>
                      @else
                        <option value="CAT">CAT</option>
                        <option value="Examination">Examination</option>
                      @endif
                    </select>
                    @error('book_reason')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Lec Booked Error  --}}
                  @error('lecturer_booked')
                      <div class="invalid-feedback col-md-12">
                        {{ $message }}
                      </div>
                  @enderror

                  </div>
                </div>
              <div class="card-footer">
                  <a href=" #" wire:click="cancel" class="btn btn-danger">
                    <i class="bi bi-arrow-left"></i> Back
                  </a>
                  @if($this->user_id==1)
                    <button type="submit" class="btn btn-primary" {{ $this->vacancies <= 0 || $this->vacancies<$this->number_occupants ? 'disabled' : '' }}>
                        <i class="bi-icons bi-bookmark-plus-fill"></i> Confirm Booking
                    </button>
                  @else
                    <button type="submit" class="btn btn-warning">
                        <i class="bi-icons bi-bookmark-plus-fill"></i> Confirm High Priority Booking
                    </button>
                  @endif
              </div>
            
          </form>
      </div>
    @endif

    {{-- Table --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">All Free Rooms Available</h3>
        <div class="card-tools">
          <p class="d-inline-block me-2">Filters: </p>
          {{-- Search Date form --}}
          <form class="d-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <input wire:model.live.debounce.100ms="search_date" type="date" name="search_date"
                    class="form-control {{ $errors->has('search_date') ? 'is-invalid' : '' }}" value="{{ old('search_date') }}">
                    @error('search_date')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
              </div>
          </form>

          {{-- Building Drop Down form --}}
          <form class="d-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <select wire:model.live.debounce.100ms="search_building" type="text" name="search_building"
                    class="form-control {{ $errors->has('search_building') ? 'is-invalid' : '' }}" >
                      @foreach ($buildings as $building)
                        <option value="{{ $building->id}}">{{$building->building_name}}</option>
                      @endforeach
                  </select>
                    @error('search_building')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
              </div>
          </form>

          {{-- Search form --}}
          <form class="d-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <input wire:model.live.debounce.700ms="search" type="text" name="search"
                    class="form-control {{ $errors->has('search') ? 'is-invalid' : '' }}"
                    placeholder="Search Rooms in Building" 
                    autofocus>
                    @error('search')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
              </div>
          </form>
          
          {{-- Link to reset --}}
          <a href="#"  wire:click="clearSearch"
            class="btn btn-success" 
            title="Reset">
            <i class="bi bi-arrow-clockwise"></i>
          </a>

          {{-- Search Prompt --}}
          @if($this->search_date=='')
            <small class="text-danger d-block">Select Date to Start Search</small>
          @endif

          {{-- Time Error --}}
          @if(($endDifference = (Carbon::parse($this->now))->diffInDays(Carbon::parse($endTimeStamp = "$this->search_date" )))<0)
            <small class="text-danger d-block">The date is in the past</small>
          @endif
        </div>
      </div>      
      <!-- /.card-header -->
      
      <div class="card-body">
        @if(count($roomsAvailable)!=0)
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th style="width: 10px">Number</th>
                <th>
                    <a href="#" wire:click="orderBy('building_name')">
                        Building Name
                    </a>
                    @if($orderDirection1=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th>
                    <a href="#" wire:click="orderBy('room_name')">
                        Room Name
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th>Day Of Week</th>
                <th>
                    <a href="#" wire:click="orderBy('start_time_id')">
                        Free From
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th>Free To</th>
                @if($this->search_date!="")
                  <th>Capacity</th>
                  <th>Book Room</th>
                @endif
              </tr>
            </thead>        
            <tbody>
              @foreach($roomsAvailable as $roomAvailable)
                {{-- Math to check the difference between the times that are there rn --}}
                @if(($endDifference = (Carbon::parse($this->now))->diffInSeconds(Carbon::parse($endTimeStamp = "$this->search_date"." $roomAvailable->end_time" )))>=0)
                  <tr>
                    {{-- Can have {{ $loop->iteration }} --}}
                    <td>{{$loop->iteration}}</td>
                    <td> {{$roomAvailable->building_name}}</td>
                    <td>{{$roomAvailable->room_name}}</td>
                    <td>{{$roomAvailable->lesson_day}}</td>
                    <td>{{$roomAvailable->start_time}}</td>
                    <td>{{$roomAvailable->end_time}}</td>
                    {{-- If you selected a search date we can do the vacancy math --}}
                    @if($this->search_date!="")
                      {{-- This is what is there for students --}}
                      @if($this->user_id==1)
                        <td>{{$roomAvailable->capacity }}</td>
                        {{-- Show the booking form --}}
                        <td>
                          <div class="btn-group" role="group">
                            <a href="#" wire:click="showBookForm({{$roomAvailable->building_id}},
                              {{$roomAvailable->room_id}},
                              '{{$roomAvailable->start_time}}',
                              '{{$roomAvailable->end_time}}',
                              {{ $roomAvailable->capacity}})"
                              class="btn btn-dark btn-sm" 
                              title="Book">
                              <i class="bi bi-bookmark-plus-fill"></i>
                            </a>
                          </div>
                        </td>
                      {{-- This is what a lecturer would see --}}
                      @else
                        @if(($this->lecBooked==0))
                          <td>{{ $roomAvailable->capacity }}</td>
                          {{-- Show the booking form --}}
                          <td>
                            <div class="btn-group" role="group">
                              <a href="#" wire:click="showBookFormPrivileged({{$roomAvailable->building_id}},
                                {{$roomAvailable->room_id}},
                                '{{$roomAvailable->start_time}}',
                                '{{$roomAvailable->end_time}}',
                                {{ $roomAvailable->capacity }})"
                                class="btn btn-danger btn-sm" 
                                title="Book">
                                <i class="bi bi-bookmark-plus-fill"></i>
                              </a>
                            </div>
                          </td>
                        @else
                          <td colspan="2">
                            <span class="text-danger">This Room is fully booked</span>
                          </td>                      
                        @endif
                      @endif
                    @endif
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        @else
          <tbody>
            <p class="text-danger">No Room or Building matches the search key</p>
          </tbody>
        @endif
      </div>
      <!-- /.card-body -->
      <div class="card-footer">
        {{-- Pagination --}}
          <div class="mt-3">
                {{ $roomsAvailable->links('pagination::bootstrap-5') }}
          </div>
      </div>
    </div>

</div>