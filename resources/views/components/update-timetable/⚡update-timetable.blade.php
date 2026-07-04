<?php

use App\Models\BaseBooking;
use App\Models\Building;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Rules\AlphaSpaces;
use App\Rules\CourseSessionData;
use App\Rules\DateValid;
use App\Rules\DayOfWeek;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
  private const UNDO_DELETE_BASE_BOOKING_SECONDS = 30;

    use WithPagination;
    protected $paginationTheme = "bootstrap";
    public $search;
    public $smallDevice=False;
    public $orderDirection1 = "asc";
    public $orderDirection2 = "asc";
    public $orderDirectionTime = "asc";
    public $id;
    public $building_id;
    public $room_name;
    public $course, $semester, $academic_year, $academic_session, $subject, $course_number, $unit_name, $lesson_day;
    public $start_time_id, $end_time_id, $room_id;
    public $orderField;
    public $showForm = false;
    public $isEditing = false;
    public $undoBaseBookingId = null;
    public $undoBaseBookingExpiresAt = null;

    public function render()
    {
      // Get Building and Room Details
      $buildings = Building::orderBy('building_name')->get();
      $rooms = Room::query()
        ->when($this->building_id, fn($q) => $q->where('building_id', $this->building_id))
        ->orderBy('room_name')
        ->get();

      // Get TimeSlot Details
      $timeSlots = TimeSlot::all();
      
      // Get Base Bookings
      $baseBookings = BaseBooking::query()
      ->join('rooms','base_bookings.room_id','=','rooms.id')
      ->join('buildings','rooms.building_id','=','buildings.id')
      ->where(function($q){
        $q->where('rooms.room_name','like',"%{$this->search}%")
        ->orWhere('buildings.building_name','like',"%{$this->search}%");
      })
      ->select('base_bookings.*','rooms.room_name','buildings.building_name')
      ->orderBy('buildings.building_name',$this->orderDirection1)
      ->orderBy('rooms.room_name',$this->orderDirection2)
      ->orderByRaw("array_position(ARRAY['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'], base_bookings.lesson_day)")
      ->orderBy('base_bookings.start_time_id',$this->orderDirectionTime)
      ->paginate(env('PAGINATION_COUNT',50));
    
      return view('components.update-timetable.⚡update-timetable',compact('baseBookings','timeSlots','rooms','buildings'));
    }

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
     * Reset the form
     * Set show form to true
     */
    public function add(){
        $this->reset();
      $this->building_id = Building::query()->orderBy('building_name')->value('id');
        $this->showForm = true;
    }

    public function cancel(){
        $this->reset();
        $this->showForm = false;
    }

    /**
     * Get Time Conflict
     * Check the base boooking table where the room_is is the room_id we are working with
     * Check the lesson day is the day we are working with
     * Check the Id of the booking is not the one we are currently editing
     * Check Two things:
          *If the start_time_id on the row is less than the end time of what we have there is a conflict, if something starts earlier than we finish we should not book
          *IF the end_time_id on the row is more than the start time of the booking we should not book, we cannot book before they end their class
    */
    public function hasTimeConflict(){
      return BaseBooking::where('room_id',$this->room_id)
      ->where('lesson_day',$this->lesson_day)
      ->when($this->id,fn($q) => $q->where('id','!=',$this->id))
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
     * Validation rules
     */
    public function rules(){
        $course_data = new CourseSessionData();
        return [
        'building_id' => ['required'],
            'room_id' => ['required'],
            'course' => ['required','max:30',$course_data],
            'semester' => ['required','max:30',$course_data],
            'academic_year' => ['required','max:30',new DateValid()],
            'academic_session' => ['required','max:30',$course_data],
            'subject' => ['required','max:30',$course_data],
            'course_number' => ['required','max:30',$course_data],
            'unit_name' => ['required','max:160',$course_data],
            'lesson_day' => ['required','max:30',new DayOfWeek()]
        ];
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
     * Store the values 
    */
    public function store()
    {
        // Validation
        $this->validate();

        // Get if timing conflict
        if($this->hasTimeConflict()){
          $this->addError('start_time_id','This room already has a booking that overlaps with the selected time on ' . $this->lesson_day . '.');
          return;
        }
        
        // Get if wrong End Time Logic
        if($this->backwardsTimeLogic()){
          $this->addError('end_time_id','This time is less than the specified start time indicated .');
          return;
        }

        // Save
        $baseBooking = BaseBooking::create([
          'course' =>$this->course,
          'semester' =>$this->semester,
          'academic_year' =>$this->academic_year,
          'academic_session' =>$this->academic_session,
          'subject' =>$this->subject,
          'course_number' =>$this->course_number,
          'unit_name' =>$this->unit_name,
          'lesson_day' =>$this->lesson_day,
          'start_time_id'=>$this->start_time_id,
          'end_time_id'=>$this->end_time_id,
          'room_id'=>$this->room_id
        ]);
        $baseBooking->save();

        // Show the table
        $this->reset();
        $this->showForm = false;

        // Give a message back to the user
        session()->flash('success','Base Booking Added Successfully');
    }

    /**
     * To edit
     */
    public function edit($id)
    {
        // Select based on id
        $baseBooking = BaseBooking::findOrFail($id);
        $this->id = $id;
      $this->building_id = Room::where('id', $baseBooking->room_id)->value('building_id');
        $this->room_id = $baseBooking->room_id;
        $this->course = $baseBooking->course; 
        $this->semester = $baseBooking->semester; 
        $this->academic_year = $baseBooking->academic_year; 
        $this->academic_session = $baseBooking->academic_session; 
        $this->subject = $baseBooking->subject; 
        $this->course_number = $baseBooking->course_number; 
        $this->unit_name = $baseBooking->unit_name; 
        $this->lesson_day = $baseBooking->lesson_day; 
        $this->start_time_id = $baseBooking->start_time_id; 
        $this->end_time_id = $baseBooking->end_time_id; 
        $this->showForm = true;
        $this->isEditing = true;
    }

    public function updatedBuildingId($value)
    {
      if (empty($value)) {
        $this->room_id = null;
        return;
      }

      $this->room_id = Room::where('building_id', $value)->orderBy('room_name')->value('id');
    }

    /**
     * Update method
     */
    public function update($id)
    {
      // Validate
      $this->validate();

      // Get if time conflict
      if($this->hasTimeConflict()){
        $this->addError('start_time_id','This room already has a booking that overlaps with the selected time on ' . $this->lesson_day . '.');
        return;
      }
    
      // Get if wrong End Time Logic
      if($this->backwardsTimeLogic()){
        $this->addError('end_time_id','This time is less than the specified start time indicated .');
        return;
        }

      // Get the basebooking needed
      $baseBooking = BaseBooking::findOrFail($id);

      $baseBooking->course = $this->course;
      $baseBooking->semester = $this->semester;
      $baseBooking->academic_year = $this->academic_year;
      $baseBooking->academic_session = $this->academic_session;
      $baseBooking->subject = $this->subject;
      $baseBooking->course_number = $this->course_number;
      $baseBooking->unit_name = $this->unit_name;
      $baseBooking->lesson_day = $this->lesson_day;
      $baseBooking->start_time_id = $this->start_time_id;
      $baseBooking->end_time_id = $this->end_time_id;
      $baseBooking->room_id=$this->room_id;
      $baseBooking->save();

      // Update the state of our variables
      $this->reset();
      $this->isEditing=false;
      $this->showForm=false;
      
      session()->flash('success','Sub Speciality Updated Successfully');
        
    }

    /**
     * Delete an item
     */
    public function destroy($id)
    {
        try{
            $baseBooking = BaseBooking::findOrFail($id);
            $payload = [
              'course' => $baseBooking->course,
              'semester' => $baseBooking->semester,
              'academic_year' => $baseBooking->academic_year,
              'academic_session' => $baseBooking->academic_session,
              'subject' => $baseBooking->subject,
              'course_number' => $baseBooking->course_number,
              'unit_name' => $baseBooking->unit_name,
              'lesson_day' => $baseBooking->lesson_day,
              'start_time_id' => $baseBooking->start_time_id,
              'end_time_id' => $baseBooking->end_time_id,
              'room_id' => $baseBooking->room_id,
            ];
            $baseBooking->delete();
            $this->registerUndoDeletedBaseBooking((int) $id, $payload);

            // Return to the page and wipe everything.
            // return redirect()->route('baseBookings.index')->with('success','Base Booking Deleted Successfully');

            // Return to the page and retain search terms
            session()->flash('warning','Base Booking Deleted. Undo is available for a short time.');
        }catch(QueryException $e){
            Log::error($e);
            session()->flash('error','Cannot Delete this Base Booking');
        }
    }

    public function undoDeletedBaseBooking()
    {
      if (empty($this->undoBaseBookingId)) {
        session()->flash('error', 'No recent base booking deletion found to undo.');
        return;
      }

      $cacheKey = $this->undoDeletedBaseBookingCacheKey((int) $this->undoBaseBookingId);
      $payload = Cache::pull($cacheKey);

      if (! is_array($payload)) {
        $this->undoBaseBookingId = null;
        $this->undoBaseBookingExpiresAt = null;
        session()->flash('error', 'Undo window has expired or booking data is unavailable.');
        return;
      }

      BaseBooking::create($payload);

      $this->undoBaseBookingId = null;
      $this->undoBaseBookingExpiresAt = null;
      session()->flash('success', 'Base booking restored successfully.');
    }

    private function registerUndoDeletedBaseBooking(int $deletedId, array $payload): void
    {
      $this->undoBaseBookingId = $deletedId;
      $this->undoBaseBookingExpiresAt = now()->addSeconds(self::UNDO_DELETE_BASE_BOOKING_SECONDS)->timestamp;

      Cache::put(
        $this->undoDeletedBaseBookingCacheKey($deletedId),
        $payload,
        now()->addSeconds(self::UNDO_DELETE_BASE_BOOKING_SECONDS)
      );
    }

    private function undoDeletedBaseBookingCacheKey(int $deletedId): string
    {
      return 'base-bookings:undo-delete:' . $deletedId;
    }
}
?>

<div class="w-full px-4 sm:px-8 max-w-none bg-[#F2E6D9] dark:bg-[#0a0a0a] min-h-screen font-sans">
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

    @if($undoBaseBookingId && $undoBaseBookingExpiresAt)
      <div
        x-data="{
          expiry: {{ (int) $undoBaseBookingExpiresAt }},
          remaining: 0,
          timer: null,
          init() {
            const tick = () => {
              const nowTs = Math.floor(Date.now() / 1000);
              this.remaining = Math.max(0, this.expiry - nowTs);
              if (this.remaining <= 0 && this.timer) {
                clearInterval(this.timer);
              }
            };
            tick();
            this.timer = setInterval(tick, 1000);
          }
        }"
        class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2"
        role="alert"
      >
        <span>
          Base booking deleted. Undo available for
          <strong x-text="remaining"></strong>s.
        </span>
        <button
          type="button"
          class="btn btn-sm btn-outline-dark"
          wire:click="undoDeletedBaseBooking"
          x-bind:disabled="remaining <= 0"
        >
          Undo delete
        </button>
      </div>
    @endif
    
    @if($showForm)
      <div class="fixed inset-0 bg-black/30 backdrop-blur-[1px] z-40" wire:click="cancel"></div>
    @endif

    {{-- Right drawer form --}}
    <div class="fixed top-0 right-0 h-full w-full max-w-xl z-50 transform transition-transform duration-300 bg-[#02338D]/95 backdrop-blur-md text-white shadow-2xl border-l border-[#02338D] {{ $showForm ? 'translate-x-0' : 'translate-x-full' }}">
      <div class="h-full overflow-y-auto">
        <div class="px-5 py-4 border-b border-white/20 flex items-center justify-between">
          <div>
            <p class="text-xs font-sans text-gray-300 tracking-wider uppercase">Timetable Form</p>
          </div>
          <button type="button" wire:click="cancel" class="inline-flex items-center justify-center rounded-lg border border-white/30 px-3 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors" title="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form wire:submit="{{$isEditing ? "update($id)" : "store"}}">
            @csrf
            <div class="p-4 sm:p-6">
              <div class="mb-4 text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">{{$isEditing ? "Edit" : "Add"}} Base Booking</div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Building --}}
                <div>
                    <label for="building_id" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Building</label>
                    <select required wire:model.live="building_id" name="building_id" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-0 focus:border-[#c99d3b] @error('building_id') is-invalid @enderror" value="{{old('building_id')}}">
                      <option value="" class="text-black">-- Select Building --</option>
                      @foreach ( $buildings as $building )
                        <option value="{{ $building->id }}" class="text-black">
                          {{ $building->building_name }}
                        </option>
                      @endforeach
                    </select>
                  @error('building_id')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Room Name --}}
                <div>
                    <label for="room_id" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Room Name</label>
                    <select required wire:model="room_id" name="room_id" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-0 focus:border-[#c99d3b] @error('room_id') is-invalid @enderror" value="{{old('room_id')}}">
                      <option value="" class="text-black">-- Select Room --</option>
                      @foreach ( $rooms as $room )
                        <option value="{{ $room->id }}" class="text-black">
                          {{ $room->room_name }}
                        </option>
                      @endforeach
                    </select>
                  @error('room_id')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                

                {{-- Course --}}
                                <div>
                  <label for="course" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Course</label>
                  <input required wire:model="course" type="text" name="course" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('course')}}">
                  @error('course')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
                </div>

                {{-- Semester --}}
                <div>
                  <label for="semester" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Semester</label>
                  <input required wire:model="semester" type="text" name="semester" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('semester')}}">
                  @error('semester')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Academic Year --}}
                {{-- Academic Session --}}
                <div>
                  <label for="academic_session" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Academic Session</label>
                  <input required wire:model="academic_session" type="text" name="academic_session" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('academic_session')}}">
                  @error('academic_session')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Academic Year --}}
                <div>
                  <label for="academic_year" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Academic Year</label>
                  <input required wire:model="academic_year" type="text" name="academic_year" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('academic_year')}}">
                  @error('academic_year')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                
                {{-- Subject --}}
                <div>
                  <label for="subject" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Subject</label>
                  <input required wire:model="subject" type="text" name="subject" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('subject')}}">
                  @error('subject')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Course Number--}}
                <div>
                  <label for="course_number" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Course Number</label>
                  <input required wire:model="course_number" type="text" name="course_number" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('course_number')}}">
                  @error('course_number')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Unit Name --}}
                <div>
                  <label for="unit_name" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Unit Name</label>
                  <input required wire:model="unit_name" type="text" name="unit_name" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('unit_name')}}">
                  @error('unit_name')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Lesson Day --}}
                <div>
                  <label for="lesson_day" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Lesson Day</label>
                  <input required wire:model="lesson_day" type="text" name="lesson_day" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-300 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('lesson_day')}}">
                  @error('lesson_day')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                {{-- Start Time --}}
                <div>
                  <label for="start_time_id" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">Start Time</label>
                  <select required wire:model="start_time_id" name="start_time_id" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('start_time_id')}}">
                    @foreach ($timeSlots as $timeSlot)
                      @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00")
                        <option value="{{ $timeSlot->id }}" class="text-black">
                          {{ $timeSlot->start_time }}
                        </option>
                      @endif
                    @endforeach
                  </select>
                  @error('start_time_id')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- End Time --}}
                <div>
                  <label for="end_time_id" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium mb-1">End Time</label>
                  <select required wire:model="end_time_id" name="end_time_id" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('end_time_id')}}">
                    @foreach ( $timeSlots as $timeSlot )
                      @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00")
                        <option value="{{ $timeSlot->id }}" class="text-black">
                          {{ $timeSlot->end_time }}
                        </option>
                      @endif
                    @endforeach
                  </select>
                  @error('end_time_id')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                </div>
              </div>
            <div class="px-4 py-3 border-t border-white/20 flex flex-wrap items-center gap-3">
              <a href=" #" wire:click="cancel" class="inline-flex items-center justify-center rounded-lg border border-white/30 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                  <i class="bi bi-arrow-left"></i> Back
                </a>
              <button type="submit" class="w-full bg-white text-[#02338D] font-bold font-sans py-3 rounded-lg shadow-md transition duration-150 cursor-pointer hover:bg-gradient-to-r hover:from-[#0048AD] hover:to-[#FF383C] hover:text-white">
                    <i class="bi-icons {{ $isEditing ? 'bi-save' : 'bi-bookmark-plus-fill' }}"></i> {{ $isEditing ? "Save Changes" : "Submit" }}
                </button>
            </div>
          
        </form>
      </div>
    </div>

    {{-- Table --}}
    <div>
      <div>
        <div class="bg-white dark:bg-transparent border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 rounded-xl p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
          <h3 class="m-0 text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">All Base Bookings</h3>
          <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">

          {{-- Update the timetable fully --}}
          <form  class="d-none d-md-inline-block me-2"
          action="{{ route('baseBookings.updateFull') }}" method="POST">
            <button type="submit" class="d-none d-md-inline-block bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-sm font-medium py-2 px-4 rounded-lg">
                <i class="bi bi-plus-circle"></i> Update Full Timetable
            </button>
          </form>

          {{-- Add a base booking --}}
          {{-- <a href="#" wire:click="add" class="d-none d-md-inline-block bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-sm font-medium py-2 px-4 rounded-lg"> --}}
            {{-- <i class="bi bi-plus-circle"></i> Add  Base Booking --}}
          {{-- </a> --}}

          {{-- Search form --}}
          <form class="d-none d-md-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <input wire:model.live.debounce.700ms="search" type="text" name="search"
                    class="w-full sm:w-64 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none {{ $errors->has('search') ? 'is-invalid' : '' }}"
                    placeholder="Search Base Bookings">
                    @error('search')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
              </div>
          </form>
          
          {{-- Link to reset --}}
          <a href="#"  wire:click="clearSearch"
            class="d-md-inline-flex d-none items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]" 
            title="Reset">
            <i class="bi bi-arrow-clockwise"></i>
          </a>

          {{-- Link to show the filter icon--}}
          <a href="#"  wire:click="showHidden"
            class="d-md-none d-inline-flex items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]" 
            title="Show Hidden">            
            <i class="bi bi-funnel-fill"></i>
          </a>
        </div>

        @if($this->smallDevice)
          <div class="d-block ms-2 row">
            <div class="col-md-4 mt-4">
              {{-- Update the timetable fully --}}
              <form  class="me-2 pt-4"
              action="{{ route('baseBookings.updateFull') }}" method="POST">
                <button type="submit" class="btn btn-primary btn-sm inline-flex w-full items-center justify-center rounded-lg border border-[#1b1b18]/20 bg-[#d4d4d4]/90 text-base font-semibold text-[#1b1b18] transition hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] sm:w-auto">
                    <i class="bi bi-plus-circle"></i>  Update Full Timetable
                </button>
              </form>


              {{-- Add a base booking --}}
              <div class="col-md-4 my-2 me-2">
                  {{-- <a href="#" wire:click="add" class="btn btn-primary btn-sm inline-flex w-full items-center justify-center rounded-lg border border-[#1b1b18]/20 bg-[#d4d4d4]/90 text-base font-semibold text-[#1b1b18] transition hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] sm:w-auto"> --}}
                    {{-- <i class="bi bi-plus-circle"></i>  Add  Base Booking --}}
                  {{-- </a> --}}
              {{-- </div> --}}

              {{-- Search form --}}
              <div class="col-md-4 my-2 me-2">
                <form class="me-2">
                    <div class="input-group input-group-sm">
                        {{--  show inline error messages --}}
                        <input wire:model.live.debounce.700ms="search" type="text" name="search"
                          class="form-control {{ $errors->has('search') ? 'is-invalid' : '' }}"
                          placeholder="Search Base Bookings" >
                          @error('search')
                            <div class="invalid-feedback">
                              {{ $message }}
                            </div>
                          @enderror
                    </div>
                </form>
              </div>
              
              {{-- Link to reset --}}
              <div class="col-md-4 my-2 me-2">
                <a href="#"  wire:click="clearSearch"
                  class="btn btn-success btn-sm" 
                  title="Reset">
                  <i class="bi bi-arrow-clockwise"></i>
                  Reset Search
                </a>
              </div>
            </div>
          </div>
        @endif
      </div>      
      <!-- /.card-header -->
      
      <div>
        @if(count($baseBookings)!=0)
        <div class="w-full bg-transparent border border-[#1d2d54]/10 rounded-xl overflow-hidden shadow-xs mt-4">
          <div class="quick-access-table-wrap table-responsive">
          <table class="w-full text-sm font-sans border-collapse">
            <thead>
              <tr class="bg-[#941c1c] text-white font-sans text-sm tracking-wide font-semibold text-left">
                <th class="px-3 py-3" style="width: 10px">Number</th>
                <th class="px-3 py-3">
                    <a href="#" wire:click="orderBy('building_name')">
                        Building Name
                    </a>
                    @if($orderDirection1=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th class="px-3 py-3">
                    <a href="#" wire:click="orderBy('room_name')">
                        Room Name
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th class="px-3 py-3">Lesson Day</th>
                <th class="px-3 py-3">
                    <a href="#" wire:click="orderBy('start_time_id')">
                        Time
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th class="px-3 py-3">Subject</th>
                <th class="px-3 py-3">Course</th>
                <th class="px-3 py-3">Unit Name</th>
                <th class="px-3 py-3">Actions</th>
              </tr>
            </thead>        
            <tbody class="bg-white/40 dark:bg-[#161615]/40 backdrop-blur-md">
              @foreach($baseBookings as $baseBooking)
              <tr class="border-b border-[#1d2d54]/5 hover:bg-white/30 transition-colors">
                {{-- Can have {{ $loop->iteration }} --}}
                <td class="px-3 py-3">{{$loop->iteration}}</td>
                <td class="px-3 py-3"> {{$baseBooking->building_name}}</td>
                <td class="px-3 py-3"><span class="text-[#c99d3b] bg-[#c99d3b]/10 border border-[#c99d3b]/30 font-semibold font-mono text-sm px-2.5 py-1 rounded-md">{{$baseBooking->room_name}}</span></td>
                <td class="px-3 py-3">{{$baseBooking->lesson_day}}</td>
                <td class="px-3 py-3">{{$baseBooking->startTimeSlot->start_time}}-{{$baseBooking->endTimeSlot->end_time}}</td>
                <td class="px-3 py-3">{{$baseBooking->subject}}</td>
                <td class="px-3 py-3">{{$baseBooking->course}}</td>
                <td class="px-3 py-3">{{$baseBooking->unit_name}}</td>
                <td class="px-3 py-3">
                  <div class="btn-group" role="group">
                    <a href="#" wire:click="edit({{$baseBooking->id}})"
                      class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-xs font-medium py-2 px-3 rounded-lg" 
                      title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" wire:click="destroy({{$baseBooking->id}})" wire:confirm="Are you sure you want to delete this timetable booking?"
                      class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-xs font-medium py-2 px-3 rounded-lg" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          </div>
        </div>
        @else
          <tbody>
            <p class="text-danger">No Room or Building matches the search key</p>
          </tbody>
        @endif
      </div>
      <!-- /.card-body -->
      <div class="mt-4 px-1" data-bs-theme="dark">
        {{ $baseBookings->links('pagination::bootstrap-5') }}
      </div>
    </div>


</div>
