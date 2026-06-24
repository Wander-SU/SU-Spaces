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
    public $search_date;
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
    // public $showForm = false;
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
     * Resetting the search
     */
    public function clearSearch(){
        $this->search = "";
    }

    /**
     * Show which data on the form
    */
    public function showBookForm($building_id,$room_id,$start_time,$end_time,$capacity)
    {
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
      $this->dispatch('initiateShowForm',[
        'showForm' => True,
        'isPrivilegedBook' => False,
        'building_id'=>$this->building_id,
        'building_name'=>$this->building_name,
        'room_capacity' => $this->room_capacity,
        'room_id' => $this->room_id,
        'room_name' => $this->room_name,
        'number_occupants' => $this->number_occupants,
        'book_date' => $this->book_date,
        'initial_start_time_id' => $this->initial_start_time_id,
        'start_time_id' => $this->start_time_id,
        'end_time_id' => $this->end_time_id,
        'initial_end_time_id' => $this->initial_end_time_id,
        'search_date'=>$this->search_date
      ]);
    }

    /**
     * The privileged version of booking
    */
    public function showBookFormPrivileged($building_id,$room_id,$start_time,$end_time,$capacity)
    {
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
      $this->dispatch('initiateShowForm',[
        'showForm' => True,
        'isPrivilegedBook' => True,
        'building_id'=>$this->building_id,
        'building_name'=>$this->building_name,
        'room_capacity' => $this->room_capacity,
        'room_id' => $this->room_id,
        'room_name' => $this->room_name,
        'number_occupants' => $this->number_occupants,
        'book_date' => $this->book_date,
        'initial_start_time_id' => $this->initial_start_time_id,
        'start_time_id' => $this->start_time_id,
        'end_time_id' => $this->end_time_id,
        'initial_end_time_id' => $this->initial_end_time_id,
        'search_date'=>$this->search_date
      ]);
    }

    /**
     * Get the day of the week that it is
     * strToTime -> Gets a timestamp
     * l -> Give the full day format
    */
    public function getDayOfWeek(String $date=null)
    {
      if($date!=null){
        $dayOfWeek = date("l",strToTime($date));
        return $dayOfWeek;
      }
      else{
        return "";
      }
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
    {{-- Only shows up if set to true --}}
    <livewire:book-forms.book-form/>

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
                      @if(auth()->user()->role->role_name=="Student")
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