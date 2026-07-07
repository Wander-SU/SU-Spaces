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
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = "bootstrap";
    #[Url(as: 'q', except: '')]
    public $search = '';
    public $search_date;
    #[Url(as: 'building', except: '')]
    public $search_building = '';
    #[Url(as: 'from', except: '')]
    public $search_from_time = "";
    #[Url(as: 'to', except: '')]
    public $search_to_time = "";
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
    public $smallDevice=False;
    // public $showForm = false;
    public $lecBooked=0;
    public $isPrivilegedBook;
    public $vacancies;  
    public $room_capacity;
    public $initial_start_time_id;
    public $initial_end_time_id;
    public $now;
    private const QUICK_ACCESS_TIMEZONE = 'Africa/Nairobi';

    public function render()
    {
      // Make the search date today
      $this->search_date = Carbon::now(self::QUICK_ACCESS_TIMEZONE)->format('Y-m-d');

      // Get Room Details
      $rooms = Room::all();

      // Get TimeSlot Details
      $timeSlots = TimeSlot::all();

      // Get buildings
      $buildings = Building::all();

      // Get the day of the week
      $dayOfWeek = $this->getDayOfWeek($this->search_date);

      // Get the date and time rn
      $this->now = Carbon::now(self::QUICK_ACCESS_TIMEZONE)->timestamp;
      
      // Get Rooms Available;
      $items = Room::query()
      ->join('buildings','buildings.id','=','rooms.building_id')
      ->crossjoin(DB::raw("(select 'Monday' as lesson_day union all 
      select 'Tuesday' union all 
      select 'Wednesday' union all 
      select 'Thursday' union all 
      select 'Friday' union all 
      select 'Saturday') as d"))
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
      ->where('rooms.room_name','like',"%$this->search%")
      ->where('d.lesson_day','like',"%$dayOfWeek%")
      ->when($this->search_building !== '' && $this->search_building !== null, function ($query) {
        $query->where('rooms.building_id', (int) $this->search_building);
      })
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
      ->orderByRaw("array_position(ARRAY['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'], d.lesson_day)")
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

      // Apply optional time window filters on consolidated availability
      if($this->search_from_time !== "" || $this->search_to_time !== ""){
        $fromTime = $this->search_from_time;
        $toTime = $this->search_to_time;

        $filtered = $filtered->filter(function($roomAvailable) use($fromTime,$toTime){
          $startTime = data_get($roomAvailable, 'start_time');
          $endTime = data_get($roomAvailable, 'end_time');

          if($fromTime !== "" && $toTime !== ""){
            return $startTime >= $fromTime && $endTime <= $toTime;
          }

          if($fromTime !== ""){
            return $startTime >= $fromTime;
          }

          return $endTime <= $toTime;
        })->values();
      }

      // Always present available rooms in chronological order (earliest first)
      $filtered = $filtered->sortBy(function($roomAvailable){
        $startTime = data_get($roomAvailable, 'start_time');
        $endTime = data_get($roomAvailable, 'end_time');
        $buildingName = data_get($roomAvailable, 'building_name');
        $roomName = data_get($roomAvailable, 'room_name');

        return $startTime . '|' . $endTime . '|' . $buildingName . '|' . $roomName;
      })->values();

      // Only include rooms whose end time has not yet passed for the selected day.
      // Apply this before pagination so empty state reflects the full filtered dataset,
      // not only the current page.
      $filtered = $filtered->filter(function ($roomAvailable) {
        return (Carbon::parse($this->now))->diffInSeconds(
          Carbon::parse("$this->search_date " . data_get($roomAvailable, 'end_time')),
          false
        ) >= 0;
      })->values();

      // Manually paginate. In "All Buildings" mode, paginate by building (1 building per page).
      $page = Paginator::resolveCurrentPage();
      $queryParams = array_filter([
        'q' => $this->search,
        'building' => $this->search_building,
        'from' => $this->search_from_time,
        'to' => $this->search_to_time,
      ], fn ($value) => $value !== '' && $value !== null);

      if ($this->search_building === '' || $this->search_building === null) {
        $groupedByBuilding = $filtered
          ->groupBy('building_name')
          ->map(fn ($items, $buildingName) => [
            'building_name' => $buildingName,
            'rooms' => $items->values(),
          ])
          ->values();

        $perPage = 1;
        $paginatedGroups = $groupedByBuilding->forPage($page, $perPage)->values();

        $roomsByBuilding = $paginatedGroups
          ->mapWithKeys(fn ($group) => [
            $group['building_name'] => $group['rooms'],
          ]);

        $roomsAvailable = new LengthAwarePaginator(
          $paginatedGroups,
          $groupedByBuilding->count(),
          $perPage,
          $page,
          [
            "path" => Paginator::resolveCurrentPath(),
            "query" => $queryParams,
          ]
        );
      } else {
        $perPage = env('PAGINATION_COUNT',50);

        $roomsAvailable = new LengthAwarePaginator(
          $filtered->forPage($page,$perPage),
          $filtered->count(),
          $perPage,
          $page,
          [
            "path" => Paginator::resolveCurrentPath(),
            "query" => $queryParams,
          ]
        );

        $roomsByBuilding = collect($roomsAvailable->items())->groupBy('building_name');
      }

      return view('components.quick-access.⚡quick-access',compact('roomsAvailable','roomsByBuilding','timeSlots','rooms','buildings'));
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
     * Show the form on small devices
    */
    public function showHidden(){
      if($this->smallDevice==False){
        $this->smallDevice = True;
      }
      else{
        $this->smallDevice = False;
      }
    }

    /**
     * Resetting the search
     */
    public function clearSearch(){
        $this->search = "";
        $this->search_building = "";
        $this->search_from_time = "";
        $this->search_to_time = "";
        $this->resetPage();
    }

    public function updatedSearchBuilding(){
      $this->resetPage();
    }

    public function updatedSearchFromTime(){}

    public function updatedSearchToTime(){}

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
      $this->number_occupants = 1;
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
      $this->showBookForm($building_id, $room_id, $start_time, $end_time, $capacity);
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

<div x-data="quickAccessDrawerState()" x-init="init()">
    {{-- Show the messages --}}
    @if (session()->has('success') && stripos((string) session('success'), 'registration successful') === false)
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

    <div class="w-full px-4 sm:px-8 max-w-none bg-[#F2E6D9] dark:bg-[#0a0a0a] min-h-screen font-sans">
      <div class="w-full">
        <div class="bg-white dark:bg-transparent border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 rounded-xl p-4 mb-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-2">
            <h3 class="m-0 text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
              Available on {{ date("l", strToTime($this->search_date)) }}, {{ Carbon::parse($this->search_date)->format('M d') }}
            </h3>
          </div>

          <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <form class="w-full sm:w-auto">
              <select
                wire:model.live.debounce.100ms="search_building"
                type="text"
                name="search_building"
                class="w-full sm:w-52 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none {{ $errors->has('search_building') ? 'is-invalid' : '' }}"
              >
                <option value="">All Buildings</option>
                @foreach ($buildings as $building)
                  <option value="{{ $building->id }}">{{ $building->building_name }}</option>
                @endforeach
              </select>
              @error('search_building')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </form>

            <form class="w-full sm:w-auto">
              <input
                wire:model.live.debounce.700ms="search"
                type="text"
                name="search"
                class="w-full sm:w-64 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none {{ $errors->has('search') ? 'is-invalid' : '' }}"
                placeholder="Search Rooms in Building"
                autofocus
              >
              @error('search')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </form>

            <form class="w-full sm:w-auto">
              <select
                wire:model.live.debounce.100ms="search_from_time"
                name="search_from_time"
                class="w-full sm:w-44 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none"
              >
                <option value="">From Time</option>
                @foreach ($timeSlots as $timeSlot)
                  @if($timeSlot->start_time >= "07:00:00" && $timeSlot->end_time <= "21:00:00" && $timeSlot->end_time != "00:00:00")
                    <option value="{{ $timeSlot->start_time }}">{{ $timeSlot->start_time }}</option>
                  @endif
                @endforeach
              </select>
            </form>

            <form class="w-full sm:w-auto">
              <select
                wire:model.live.debounce.100ms="search_to_time"
                name="search_to_time"
                class="w-full sm:w-44 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none"
              >
                <option value="">To Time</option>
                @foreach ($timeSlots as $timeSlot)
                  @if($timeSlot->start_time >= "07:00:00" && $timeSlot->end_time <= "21:00:00" && $timeSlot->end_time != "00:00:00")
                    <option value="{{ $timeSlot->end_time }}">{{ $timeSlot->end_time }}</option>
                  @endif
                @endforeach
              </select>
            </form>

            <a
              href="#"
              wire:click="clearSearch"
              class="inline-flex items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]"
              title="Reset"
            >
              <i class="bi bi-arrow-clockwise"></i>
              <span class="ms-2"></span>
            </a>

            <a
              href="#"
              wire:click="showHidden"
              class="inline-flex md:hidden items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]"
              title="Show Hidden"
            >
              <i class="bi bi-funnel-fill"></i>
            </a>
          </div>
        </div>

        @if($this->smallDevice)
          <div class="md:hidden bg-white dark:bg-transparent border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 rounded-xl p-4 mb-6 shadow-xs grid grid-cols-1 gap-3">
            <form>
              <input wire:model.live.debounce.700ms="search" type="text" name="search"
                class="w-full rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none {{ $errors->has('search') ? 'is-invalid' : '' }}"
                placeholder="Search Rooms in Building">
              @error('search')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </form>

            <form>
              <select wire:model.live.debounce.100ms="search_building" type="text" name="search_building"
                class="w-full rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none {{ $errors->has('search_building') ? 'is-invalid' : '' }}">
                <option value="">All Buildings</option>
                @foreach ($buildings as $building)
                  <option value="{{ $building->id }}">{{ $building->building_name }}</option>
                @endforeach
              </select>
              @error('search_building')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </form>

            <form>
              <select wire:model.live.debounce.100ms="search_from_time" name="search_from_time"
                class="w-full rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none">
                <option value="">From Time</option>
                @foreach ($timeSlots as $timeSlot)
                  @if($timeSlot->start_time >= "07:00:00" && $timeSlot->end_time <= "21:00:00" && $timeSlot->end_time != "00:00:00")
                    <option value="{{ $timeSlot->start_time }}">{{ $timeSlot->start_time }}</option>
                  @endif
                @endforeach
              </select>
            </form>

            <form>
              <select wire:model.live.debounce.100ms="search_to_time" name="search_to_time"
                class="w-full rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none">
                <option value="">To Time</option>
                @foreach ($timeSlots as $timeSlot)
                  @if($timeSlot->start_time >= "07:00:00" && $timeSlot->end_time <= "21:00:00" && $timeSlot->end_time != "00:00:00")
                    <option value="{{ $timeSlot->end_time }}">{{ $timeSlot->end_time }}</option>
                  @endif
                @endforeach
              </select>
            </form>

            <a href="#" wire:click="clearSearch"
              class="inline-flex items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]"
              title="Reset">
              <i class="bi bi-arrow-clockwise"></i>
              <span class="ms-2">Reset Search</span>
            </a>
          </div>
        @endif

        @if(count($roomsByBuilding) !== 0)
          @foreach($roomsByBuilding as $buildingName => $rooms)
            <section class="mb-6">
              <h2 class="font-sans font-bold text-lg text-[#1b1b18] dark:text-[#EDEDEC] mb-4 tracking-wide">
                {{ $buildingName }}
              </h2>

              <div class="w-full bg-transparent border border-[#1d2d54]/10 rounded-xl overflow-hidden shadow-xs mt-4">
                <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[880px] text-sm font-sans border-collapse">
                  <thead>
                    <tr class="bg-[#941c1c] text-white font-sans text-sm tracking-wide font-semibold text-left">
                      <th class="px-4 py-3 font-semibold text-white">Room</th>
                      <th class="px-4 py-3 font-semibold text-white">Day</th>
                      <th class="px-4 py-3 font-semibold text-white">Available Window</th>
                      <th class="px-4 py-3 font-semibold text-white text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white/40 dark:bg-[#161615]/40 backdrop-blur-md">
                    @foreach($rooms as $roomAvailable)
                      <tr class="border-b border-[#1d2d54]/5 hover:bg-white/30 transition-colors">
                        <td class="px-4 py-3 align-middle">
                          <span class="text-[#c99d3b] bg-[#c99d3b]/10 border border-[#c99d3b]/30 font-semibold font-mono text-sm px-2.5 py-1 rounded-md">
                            {{ $roomAvailable->room_name }}
                          </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A] align-middle">
                          {{ $roomAvailable->lesson_day }}
                        </td>
                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A] align-middle">
                          {{ $roomAvailable->start_time }} - {{ $roomAvailable->end_time }}
                        </td>
                        <td class="px-4 py-3 text-right align-middle">
                          <a href="#" wire:click="showBookForm({{ $roomAvailable->building_id }},
                            {{ $roomAvailable->room_id }},
                            '{{ $roomAvailable->start_time }}',
                            '{{ $roomAvailable->end_time }}',
                            {{ $roomAvailable->capacity }})"
                            x-on:click="sidebarOpen = true; selectedRoom = '{{ $roomAvailable->room_name }}'"
                            class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-xs font-medium py-2 px-4 rounded-lg"
                            title="Book">
                            Book Room
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                </div>
              </div>
            </section>
          @endforeach
        @else
          <div class="rounded-lg border border-dashed border-[#e3e3e0] p-8 text-center dark:border-[#3E3E3A]">
            <div class="mx-auto mb-5 flex h-28 w-28 items-center justify-center rounded-full bg-[#f6f6f4] dark:bg-[#232322]">
              <svg class="h-16 w-16 text-[#1b1b18] dark:text-[#EDEDEC]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M4 9h16M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
              </svg>
            </div>
            <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
              No available rooms today. Book a room in advance!
            </p>
            <div class="mt-5">
              <a href="{{ route('buildingNavigation.index') }}" class="inline-flex items-center rounded-md bg-gradient-to-r from-[#0048AD] to-[#FF383C] px-6 py-2 font-medium text-white transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#0048AD]/30">
                Find a room now
              </a>
            </div>
          </div>
        @endif

        <div class="mt-4 px-1 flex justify-end" data-bs-theme="light">
          {{ $roomsAvailable->links('pagination::bootstrap-5') }}
        </div>
      </div>

    </div>

    <div
      x-show="sidebarOpen"
      x-transition:enter="transition ease-out duration-300 transform"
      x-transition:enter-start="translate-x-full"
      x-transition:enter-end="translate-x-0"
      x-transition:leave="transition ease-in duration-200 transform"
      x-transition:leave-start="translate-x-0"
      x-transition:leave-end="translate-x-full"
      class="fixed right-2 sm:right-4 top-[4.5rem] bottom-[3.75rem] w-[calc(100vw-1rem)] sm:w-full sm:max-w-md bg-[#02338D] backdrop-blur-md border border-white/10 rounded-xl sm:rounded-2xl shadow-2xl z-50 p-6 overflow-y-auto text-white"
      style="display: none;"
    >
      <div class="h-full">
        <div class="flex items-center">
          <p class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Booking Action</p>
        </div>

        <h3 class="text-2xl font-bold font-sans text-white mt-4" x-text="selectedRoom"></h3>

        <div class="mt-4">
          <livewire:book-forms.book-form/>
        </div>
      </div>
    </div>
</div>

<script>
function quickAccessDrawerState() {
  return {
    sidebarOpen: false,
    selectedRoom: '',
    listenersBound: false,

    init() {
      this.bindDrawerListeners();
    },

    bindDrawerListeners() {
      if (this.listenersBound || !window.Livewire) {
        return;
      }

      this.listenersBound = true;

      window.Livewire.on('initiateShowForm', (payload) => {
        const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
        if (data.room_name) {
          this.selectedRoom = data.room_name;
        }
        this.sidebarOpen = true;
      });

      window.Livewire.on('initiatedHideForm', () => {
        this.sidebarOpen = false;
      });
    },
  };
}
</script>