<?php

use App\Models\BaseBooking;
use App\Models\Room;
use App\Rules\AlphaSpaces;
use App\Rules\CourseSessionData;
use App\Rules\DateValid;
use App\Rules\DayOfWeek;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    protected $paginationTheme = "bootstrap";
    public $search;
    public $orderDirection1 = "asc";
    public $orderDirection2 = "asc";
    public $orderDirectionTime = "asc";
    public $id;
    public $room_name;
    public $course, $semester, $academic_year, $academic_session, $subject, $course_number, $unit_name, $lesson_day;
    public $start_time, $end_time, $room_id;

    public $orderField;
    public $showForm = false;
    public $isEditing = false;

    public function render()
    {
      // Get Room Details
      $rooms = Room::all();
      
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
      ->orderByRaw("FIELD(base_bookings.lesson_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
      ->orderBy('base_bookings.start_time',$this->orderDirectionTime)
      ->paginate(env('PAGINATION_COUNT',50));
      
      return view('components.update-timetable.⚡update-timetable',compact('baseBookings','rooms'));
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

        if($this->orderField=="start_time"){
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
        $this->showForm = true;
    }

    public function cancel(){
        $this->reset();
        $this->showForm = false;
    }

    /**
     * Get Time Conflict
    */
    public function hasTimeConflict(){
      return BaseBooking::where('room_id',$this->room_id)
      ->where('lesson_day',$this->lesson_day)
      ->when($this->id,fn($q) => $q->where('id','!=',$this->id))
      ->where(function($q){
        $q->where('start_time','<',$this->end_time)
        ->where('end_time','>',$this->start_time);
      })
      ->exists();
    }

    /**
     * Validation rules
     */
    public function rules(){
        $course_data = new CourseSessionData();
        return [
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
     * Store the values 
    */
    public function store()
    {
        // Validation
        $this->validate();

        // Get if time error
        if($this->hasTimeConflict()){
          $this->addError('start_time','This room already has a booking that overlaps with the selected time on ' . $this->lesson_day . '.');
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
          'start_time'=>$this->start_time,
          'end_time'=>$this->end_time,
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
        $this->room_id = $baseBooking->room_id;
        $this->course = $baseBooking->course; 
        $this->semester = $baseBooking->semester; 
        $this->academic_year = $baseBooking->academic_year; 
        $this->academic_session = $baseBooking->academic_session; 
        $this->subject = $baseBooking->subject; 
        $this->course_number = $baseBooking->course_number; 
        $this->unit_name = $baseBooking->unit_name; 
        $this->lesson_day = $baseBooking->lesson_day; 
        $this->start_time = $baseBooking->start_time; 
        $this->end_time = $baseBooking->end_time; 
        $this->showForm = true;
        $this->isEditing = true;
    }

    /**
     * Update method
     */
    public function update($id)
    {
      // Validate
      $this->validate();

      // Get if time error
        if($this->hasTimeConflict()){
          $this->addError('start_time','This room already has a booking that overlaps with the selected time on ' . $this->lesson_day . '.');
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
      $baseBooking->start_time = $this->start_time;
      $baseBooking->end_time = $this->end_time;
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
            $baseBooking->delete();

            // Return to the page and wipe everything.
            // return redirect()->route('baseBookings.index')->with('success','Base Booking Deleted Successfully');

            // Return to the page and retain search terms
            session()->flash('success','Base Booking Deleted Successfully');
        }catch(QueryException $e){
            Log::error($e);
            session()->flash('error','Cannot Delete this Base Booking');
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
    
    {{-- Form --}}
    @if($showForm)
    <div class="card card-primary card-outline mb-4">
        <div class="card-header">
            <div class="card-title">{{$isEditing ? "Edit" : "Add"}} Base Booking</div>
        </div>
        <form wire:submit="{{$isEditing ? "update($id)" : "store"}}">
            @csrf
            <div class="card-body">
              <div class="row">
                {{-- Room Name --}}
                <div class="col-md-4 mb-3">
                    <label for="room_id" class="form-label">Room Name</label>
                    <select required wire:model="room_id" name="room_id" class="form-control @error('room_id') is-invalid @enderror" value="{{old('room_id')}}">
                      @foreach ( $rooms as $room )
                        <option value="{{ $room->id }}">
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
                <div class="col-md-4 mb-3">
                  <label for="course" class="form-label">Course</label>
                  <input required wire:model="course" type="text" name="course" class="form-control @error('name') is-invalid @enderror" value="{{old('course')}}">
                  @error('course')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
                </div>

                {{-- Semester --}}
                <div class="col-md-4 mb-3">
                  <label for="semester" class="form-label">Semester</label>
                  <input required wire:model="semester" type="text" name="semester" class="form-control @error('name') is-invalid @enderror" value="{{old('semester')}}">
                  @error('semester')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Academic Year --}}
                <div class="col-md-4 mb-3">
                  <label for="academic_year" class="form-label">Academic Year</label>
                  <input required wire:model="academic_year" type="text" name="academic_year" class="form-control @error('name') is-invalid @enderror" value="{{old('academic_year')}}">
                  @error('academic_year')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Academic Session --}}
                <div class="col-md-4 mb-3">
                  <label for="academic_session" class="form-label">Academic Session</label>
                  <input required wire:model="academic_session" type="text" name="academic_session" class="form-control @error('name') is-invalid @enderror" value="{{old('academic_session')}}">
                  @error('academic_session')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                
                {{-- Subject --}}
                <div class="col-md-4 mb-3">
                  <label for="subject" class="form-label">Subject</label>
                  <input required wire:model="subject" type="text" name="subject" class="form-control @error('name') is-invalid @enderror" value="{{old('subject')}}">
                  @error('subject')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Course Number--}}
                <div class="col-md-4 mb-3">
                  <label for="course_number" class="form-label">Course Number</label>
                  <input required wire:model="course_number" type="text" name="course_number" class="form-control @error('name') is-invalid @enderror" value="{{old('course_number')}}">
                  @error('course_number')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Unit Name --}}
                <div class="col-md-4 mb-3">
                  <label for="unit_name" class="form-label">Unit Name</label>
                  <input required wire:model="unit_name" type="text" name="unit_name" class="form-control @error('name') is-invalid @enderror" value="{{old('unit_name')}}">
                  @error('unit_name')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Lesson Day --}}
                <div class="col-md-4 mb-3">
                  <label for="lesson_day" class="form-label">Lesson Day</label>
                  <input required wire:model="lesson_day" type="text" name="lesson_day" class="form-control @error('name') is-invalid @enderror" value="{{old('lesson_day')}}">
                  @error('lesson_day')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                {{-- Start Time --}}
                <div class="col-md-4 mb-3">
                  <label for="start_time" class="form-label">Start Time</label>
                  <input required wire:model="start_time" type="time" name="start_time" class="form-control @error('name') is-invalid @enderror" value="{{old('start_time')}}">
                  @error('start_time')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- End Time --}}
                <div class="col-md-4 mb-3">
                  <label for="end_time" class="form-label">End Time</label>
                  <input required wire:model="end_time" type="time" name="end_time" class="form-control @error('name') is-invalid @enderror" value="{{old('end_time')}}">
                  @error('end_time')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                </div>
              </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi-icons bi-save"></i> {{ $isEditing ? "Save Changes" : " Submit" }}
                </button>
                <a href=" #" wire:click="cancel" class="btn btn-danger">
                  <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
          
        </form>
    </div>

    @else
    {{-- Table --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">All Base Bookings</h3>
        <div class="card-tools">

          {{-- Update the timetable fully --}}
          <form  class="d-inline-block me-2"
          action="{{ route('baseBookings.updateFull') }}" method="POST">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Update Full Timetable
            </button>
          </form>

          {{-- Add a base booking --}}
          <a href="#" wire:click="add" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add  Base Booking
          </a>
          {{-- Search form --}}
          <form class="d-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <input wire:model.live.debounce.700ms="search" type="text" name="search"
                    class="form-control {{ $errors->has('search') ? 'is-invalid' : '' }}"
                    placeholder="Search Base Bookings" 
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
        </div>
      </div>      
      <!-- /.card-header -->
      
      <div class="card-body">
        @if(count($baseBookings)!=0)
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
                <th>Lesson Day</th>
                <th>
                    <a href="#" wire:click="orderBy('start_time')">
                        Time
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th>Subject</th>
                <th>Course</th>
                <th>Unit Name</th>
                <th>Actions</th>
              </tr>
            </thead>        
            <tbody>
              @foreach($baseBookings as $baseBooking)
              <tr>
                {{-- Can have {{ $loop->iteration }} --}}
                <td>{{$loop->iteration}}</td>
                <td> {{$baseBooking->building_name}}</td>
                <td>{{$baseBooking->room_name}}</td>
                <td>{{$baseBooking->lesson_day}}</td>
                <td>{{$baseBooking->start_time}}-{{$baseBooking->end_time}}</td>
                <td>{{$baseBooking->subject}}</td>
                <td>{{$baseBooking->course}}</td>
                <td>{{$baseBooking->unit_name}}</td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="#" wire:click="edit({{$baseBooking->id}})"
                      class="btn btn-warning btn-sm" 
                      title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="#" wire:click="destroy({{$baseBooking->id}})"
                          class="d-inline"
                          wire:confirm="return confirm('Are you sure you want to delete this timetable booking?');"
                          method="POST">
                      <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </a>
                  </div>
                </td>
              </tr>
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
                {{ $baseBookings->links('pagination::bootstrap-5') }}
          </div>
      </div>
    </div>

    @endif


</div>
