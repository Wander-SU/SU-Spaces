<?php

use App\Http\Controllers\BaseBookingController;
use App\Mail\BookingVoided;
use App\Models\BaseBooking;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Rules\DateGreaterThanToday;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  private const UNDO_NEW_BOOKING_SECONDS = 15;

    public $isPrivilegedBook;
    public $vacancies;  // Variable is never used as old="" rather bound to a wire:model
    public $number_occupants;
    public $search_date;
    public $building_id;
    public $building_name;
    public $start_time;
    public $end_time;
    public $room_name;
    public $book_date;
    public $book_reason="";
    public $start_time_id;
    public $end_time_id;
    public $room_id;
    public $orderField;
    public $showForm = false;
    public $room_capacity;
    public $initial_start_time_id;
    public $initial_end_time_id;
    public $now;
    public $undoBookingId = null;
    public $undoBookingExpiresAt = null;

    public function render(){
        $timeSlots = TimeSlot::all();

        return view('components.book-forms.⚡book-form',compact('timeSlots'));
    }

    #[On('initiatedHideForm')]
    public function initiatedHideForm(){
        $this->showForm = False;
    }

    /**
     * Get the book form info immediately
    */
    #[On('initiateShowForm')]
    public function initiatedShowForm($data)
    {
        $this->showForm = $data['showForm'];
        $this->isPrivilegedBook = $data['isPrivilegedBook'];
        $this->building_id = $data['building_id'];
        $this->building_name = $data['building_name'];
        $this->room_capacity = $data['room_capacity'];
        $this->room_id = $data['room_id'];
        $this->room_name = $data['room_name'];
        $this->number_occupants = $data['number_occupants'];
        $this->book_date = $data['book_date'];
        $this->initial_start_time_id = $data['initial_start_time_id'];
        $this->start_time_id = $data['start_time_id'];
        $this->end_time_id = $data['end_time_id'];
        $this->initial_end_time_id = $data['initial_end_time_id'];
        $this->search_date = $data['search_date'];
        $this->computeVacancies(); // Used to compute vacancies of specified room immediately
    }

    /**
     * Get the book from from the nav section immediately
    */
    #[On('initiateShowFormFromNav')]
    public function initiateShowFormFromNav($data)
    {
        $this->showForm = $data['showForm'];
        $this->isPrivilegedBook = $data['isPrivilegedBook'];
        $this->building_id = $data['building_id'];
        $this->building_name = $data['building_name'];
        $this->room_capacity = $data['room_capacity'];
        $this->room_id = $data['room_id'];
        $this->room_name = $data['room_name'];
        $this->number_occupants = $data['number_occupants'];
        $this->book_date = $data['book_date'];
        $this->start_time_id = $data['start_time_id'];
        $this->end_time_id = $data['end_time_id'];
        $this->initial_start_time_id=$this->start_time_id;
        $this->initial_end_time_id=$this->end_time_id;
        $this->search_date = $data['search_date'];
        $this->computeVacancies(); // Used to compute vacancies of specified room immediately
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
     * Remove the form from view
    */
    public function cancel(){
        $this->resetExcept(["search_date","search_building","room_name","building_name"]);
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

      $bookedAlready= $this->getBookCount($this->start_time_id,$this->end_time_id,$this->room_id,$this->book_date);
      if($bookedAlready>1){
        $this->showForm = false;
        $this->resetExcept(["search_date","search_building","room_name","building_name","number_occupants"]);
        session()->flash('error','The selected user already has 2 bookings at the selected time, to prevent spam, you may not book any more rooms until you void others');
        return;
        
      }

      try{
      $booking = Booking::create([
        "created_at"=>now(),
        "updated_at"=>now(),
        "attendee_count"=>$this->number_occupants,
        "status"=>$status,
        "purpose"=>$this->book_reason,
        "booking_date"=>$this->book_date,
        "room_id"=>$this->room_id,
        "user_id"=>auth()->user()->id,
        "start_time_id"=>$this->start_time_id,
        "end_time_id"=>$this->end_time_id,
      ]);
      $booking->save();
      $this->registerUndoNewBooking($booking);

      // Show the table
      $this->showForm = false;
      $this->resetExcept(["search_date","search_building","room_name","building_name","number_occupants"]);

      session()->flash("success","Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants occupants");
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
      }catch(Throwable $e){
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
            if($voidable->user->role->role_name!="Student"){
              session()->flash('error','Could not book this room because a lecturer "'.$voidable->user->name.'"has booked this room at '.$voidable->startTimeSlot->start_time.' to '.$voidable->endTimeSlot->end_time.' for: '.$voidable["purpose"] );
              $this->showForm=False;
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
        "user_id"=>auth()->user()->id,
        "start_time_id"=>$this->start_time_id,
        "end_time_id"=>$this->end_time_id,
      ]);
      $booking->save();
      $this->registerUndoNewBooking($booking);

      // Show the table
      $this->showForm = false;
      $this->resetExcept(["search_date","search_building","room_name","building_name"]);

      session()->flash("success","Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants occupants");
      }
      catch(\Throwable $e){
        session()->flash('failure',"There was a database error");
      }
    }

    public function undoNewBooking()
    {
      if (empty($this->undoBookingId)) {
        session()->flash('error', 'No recent booking found to undo.');
        return;
      }

      $booking = Booking::find($this->undoBookingId);
      if (!$booking || (int) $booking->user_id !== (int) auth()->user()->id) {
        $this->undoBookingId = null;
        $this->undoBookingExpiresAt = null;
        session()->flash('error', 'Undo window has expired or this booking is unavailable.');
        return;
      }

      $cacheKey = $this->undoNewBookingCacheKey((int) auth()->user()->id, (int) $booking->id);
      $undoAllowed = (bool) Cache::pull($cacheKey, false);
      if (!$undoAllowed || $booking->status !== 'Booked') {
        $this->undoBookingId = null;
        $this->undoBookingExpiresAt = null;
        session()->flash('error', 'Undo window has expired or this booking can no longer be undone.');
        return;
      }

      $booking->status = 'Voided';
      $booking->save();

      $this->undoBookingId = null;
      $this->undoBookingExpiresAt = null;
      session()->flash('success', 'Booking undone successfully.');
    }

    private function registerUndoNewBooking(Booking $booking): void
    {
      $this->undoBookingId = (int) $booking->id;
      $this->undoBookingExpiresAt = now()->addSeconds(self::UNDO_NEW_BOOKING_SECONDS)->timestamp;
      Cache::put(
        $this->undoNewBookingCacheKey((int) auth()->user()->id, (int) $booking->id),
        true,
        now()->addSeconds(self::UNDO_NEW_BOOKING_SECONDS)
      );
    }

    private function undoNewBookingCacheKey(int $userId, int $bookingId): string
    {
      return 'bookings:undo-new:' . $userId . ':' . $bookingId;
    }

    /**
     * Used to get the rooms that can be voided
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
     * Used to get the number of rooms that a person has booked
    */
    public function  getBookCount(int $start_time_id,int $end_time_id, int $room_id, string $prospected_date)
    {
      $utilised = Booking::query()
      ->where('start_time_id','<=',"$end_time_id")
      ->where('end_time_id','>=',"$start_time_id")
      ->where('room_id',$room_id)
      ->where('status',"Booked")
      ->where('user_id',auth()->user()->id)
      ->where('booking_date',"$prospected_date")
      ->get();

      $booked= count($utilised);
      return $booked;

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
};
?>

{{-- Start of Livewire View --}}
<div>
    @if (session()->has('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($undoBookingId && $undoBookingExpiresAt)
      <div
        x-data="{
          expiry: {{ (int) $undoBookingExpiresAt }},
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
          Booking created. Undo available for
          <strong x-text="remaining"></strong>s.
        </span>
        <button
          type="button"
          class="btn btn-sm btn-outline-dark"
          wire:click="undoNewBooking"
          x-bind:disabled="remaining <= 0"
        >
          Undo booking
        </button>
      </div>
    @endif

    @if (session()->has('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    
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
                     @if(auth()->user()->role->role_name!="Student") disabled @endif
                     type="number" min=1 name="number_occupants" max="30" class="form-control @error('number_occupants') is-invalid @enderror" value="{{old('number_occupants')}}">
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
                      <option>--Select One--</option>
                      @if(auth()->user()->role->role_name=="Student")
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

                  {{-- Edit if the error in number of occupants entered, number of vacancies etc --}}
                  @if ($this->vacancies <= 0 || $this->vacancies<$this->number_occupants)
                    <div class="invalid-feedback col-md-12">
                      <small>
                        <ul>Either:
                        <li>Vacancies less than 1</li> 
                        <li>number of occupants > number of vacancies</lli>
                        </ul>
                      </small>
                    </div>
                  @endif

                  </div>
                </div>
              <div class="card-footer">
                  <a href=" #" wire:click="cancel" class="btn btn-danger">
                    <i class="bi bi-arrow-left"></i> Back
                  </a>
                  @if(auth()->user()->role->role_name=="Student")
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
    </div>
    @endif
</div>