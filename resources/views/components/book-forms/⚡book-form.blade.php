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
      $this->isPrivilegedBook = (bool) ($data['isPrivilegedBook'] ?? false);
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
        $this->book_reason = '';
        $this->syncOccupantsWithCapacity();
        $this->computeVacancies(); // Used to compute vacancies of specified room immediately
    }

    /**
     * Get the book from from the nav section immediately
    */
    #[On('initiateShowFormFromNav')]
    public function initiateShowFormFromNav($data)
    {
        $this->showForm = $data['showForm'];
      $this->isPrivilegedBook = (bool) ($data['isPrivilegedBook'] ?? false);
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
        $this->book_reason = '';
        $this->syncOccupantsWithCapacity();
        $this->computeVacancies(); // Used to compute vacancies of specified room immediately
    }


    /**
     * Validation Rules
    */
    public function rules(){
      return [
        "number_occupants"=>["required","integer","min:1","max:" . (int) ($this->room_capacity ?? 0)],
        "book_reason"=>["required"],
        "book_date"=>["required",new DateGreaterThanToday()],
        "end_time_id"=>["required"]
      ];
    }

    private function syncOccupantsWithCapacity(): void
    {
      $capacity = (int) ($this->room_capacity ?? 0);
      if ($capacity <= 0) {
        return;
      }

      if ($this->shouldUsePriorityBooking()) {
        $this->number_occupants = $capacity;
        return;
      }

      $occupants = (int) ($this->number_occupants ?? 1);
      if ($occupants < 1) {
        $occupants = 1;
      }

      $this->number_occupants = min($occupants, $capacity);
    }

    /**
     * Remove the form from view
    */
    public function cancel(){
        $this->resetExcept(["search_date","search_building","room_name","building_name"]);
        $this->dispatch('initiatedHideForm');
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
      $isPriorityBooking = $this->shouldUsePriorityBooking();

      if (!$isPriorityBooking && (int) ($this->number_occupants ?? 0) > (int) ($this->vacancies ?? 0)) {
        $this->addError('number_occupants', 'The number of occupants exceeds the available slots');
        return;
      }

      if ($this->violatesStudentReasonRule()) {
        $this->addError('book_reason', 'For fewer than 2 occupants, reason must be Individual Study.');
        return;
      }

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

      if($this->hasExactDuplicateBooking()){
        $this->addError('booking_duplicate','You already have this exact booking. Duplicate bookings are not allowed.');
        return;
      }

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

      $this->dispatch('initiatedHideForm');
      $this->dispatch('bookingMessage', type: 'success', message: "Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants occupants");
      session()->flash("success","Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants occupants");
      }
      catch(\Throwable $e){
        session()->flash('failure',"There was a database error");
      }
    }

    /**
     * Void a booking
    */
    public function void($id,$reason){
      try{
        $bookingToBeVoided = Booking::findOrFail($id);
        $bookingToBeVoided->status = "Voided";
        $bookingToBeVoided->save();
        Mail::to($bookingToBeVoided->user->email)->queue(new BookingVoided($bookingToBeVoided,$reason));
      }catch(Throwable $e){
        session()->flash('failure',"Could not void the bookings of other students".$e->getMessage());
      }
    }

    /**
     * Privileged Booking to the database
    */
    public function bookPrivileged(){
      /**
       * If it is not meant to be a priotity booking go to normal booking.
      */
      if (!$this->shouldUsePriorityBooking()) {
        $this->book();
        return;
      }


      $this->syncOccupantsWithCapacity();

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

      if($this->hasExactDuplicateBooking()){
        $this->addError('booking_duplicate','You already have this exact booking. Duplicate bookings are not allowed.');
        return;
      }

      // Create the tuple that is needed
      try{
        // Get the total number of voidable bookings that we should do
        $toVoid = $this->getVoidable($this->start_time_id,$this->end_time_id,$this->room_id,$this->book_date);

        // Void each row of the things to be voided
        if(count($toVoid)>0){
          foreach($toVoid as $voidable){
            /**
             * If someone cannot override the booking; Admin -> Registrar -> Lecturer
             * Show an error specifying who is preventing the override
             * Form is closed
             * Return/Exit the function
            */
            if(!$this->canOverrideBooking($voidable)){
              $blockedByRole = (string) optional(optional($voidable->user)->role)->role_name;
              $blockedByName = (string) optional($voidable->user)->name;
              session()->flash(
                'error',
                'Could not complete high priority booking. '.$blockedByName.' ('.$blockedByRole.') already has this room from '
                .$voidable->startTimeSlot->start_time.' to '.$voidable->endTimeSlot->end_time.' for: '.$voidable['purpose']
              );
              $this->showForm=False;
              return;
            }
          }
          forEach($toVoid as $voidable){
            $this->void($voidable["id"],$this->book_reason);
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

      $this->dispatch('initiatedHideForm');
      $this->dispatch('bookingMessage', type: 'success', message: "Room $this->room_name @ $this->building_name successfully booked for $this->number_occupants occupants");
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
      ->whereNot('status', 'Voided')
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

    public function hasExactDuplicateBooking(): bool
    {
      return Booking::query()
        ->where('user_id', auth()->id())
        ->where('room_id', $this->room_id)
        ->where('booking_date', $this->book_date)
        ->where('start_time_id', $this->start_time_id)
        ->where('end_time_id', $this->end_time_id)
        ->where('status', 'Booked')
        ->exists();
    }

    /**
     * Function to get the vacancies
    */
    public function computeVacancies(){
      $this->vacancies = $this->room_capacity - $this->roomUtilisationUsingIds($this->start_time_id,$this->end_time_id,$this->room_id,$this->search_date);
    }

    private function violatesStudentReasonRule(): bool
    {
      $roleName = strtolower((string) optional(auth()->user()->role)->role_name);
      $occupants = (int) $this->number_occupants;
      $reason = strtolower(trim((string) $this->book_reason));

      return $roleName === 'student' && $occupants < 2 && $reason !== 'individual study';
    }

    public function shouldUsePriorityBooking(): bool
    {
      return $this->isPriorityEligibleRole() && $this->isPriorityReason();
    }

    private function isPriorityEligibleRole(): bool
    {
      $roleName = strtolower(trim((string) optional(auth()->user()->role)->role_name));

      return $roleName !== '' && $roleName !== 'student';
    }

    private function isPriorityReason(): bool
    {
      $reason = strtolower(trim((string) $this->book_reason));

      return in_array($reason, ['cat', 'examination', 'exam'], true);
    }

    private function normalizeOverrideRole(?string $roleName): string
    {
      $normalized = strtolower(trim((string) $roleName));

      if (str_contains($normalized, 'admin')) {
        return 'admin';
      }

      if (str_contains($normalized, 'registrar')) {
        return 'registrar';
      }

      if (str_contains($normalized, 'lecturer')) {
        return 'lecturer';
      }

      if (str_contains($normalized, 'student')) {
        return 'student';
      }

      return 'other';
    }

    private function isHighPriorityPurpose(?string $purpose): bool
    {
      $normalized = strtolower(trim((string) $purpose));

      return in_array($normalized, ['cat', 'exam', 'examination'], true);
    }

    private function canOverrideBooking(Booking $booking): bool
    {
      $actorRole = $this->normalizeOverrideRole((string) optional(auth()->user()->role)->role_name);
      $targetRole = $this->normalizeOverrideRole((string) optional(optional($booking->user)->role)->role_name);
      $targetIsHighPriority = $this->isHighPriorityPurpose((string) $booking->purpose);

      // Any regular booking is overridable by lecturer/registrar/admin.
      if (!$targetIsHighPriority) {
        return in_array($actorRole, ['lecturer', 'registrar', 'admin'], true);
      }

      if ($actorRole === 'admin') {
        // Admin cannot override another admin's CAT/Examination booking.
        return $targetRole !== 'admin';
      }

      if ($actorRole === 'registrar') {
        // Registrar cannot override admin/registrar CAT/Examination bookings.
        return in_array($targetRole, ['student', 'lecturer', 'other'], true);
      }

      if ($actorRole === 'lecturer') {
        // Lecturer cannot override admin/registrar/lecturer CAT/Examination bookings.
        return in_array($targetRole, ['student', 'other'], true);
      }

      return false;
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

    public function updatedNumberOccupants(){
      $this->syncOccupantsWithCapacity();
    }

    public function updatedBookReason(): void
    {
      $this->syncOccupantsWithCapacity();
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

    @php
      $drawerLabelClass = 'text-xs font-sans font-semibold text-gray-300 uppercase tracking-wider mb-1.5 block';
      $drawerInputClass = 'w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-[#c99d3b] focus:ring-1 focus:ring-[#c99d3b] transition-all font-sans';
    @endphp
    
    @if($showForm)
    <div class="font-sans text-white bg-[#02338D]">
          <div class="mb-4">
          <div class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Book Room Details</div>
          </div>
          <form wire:submit="{{ $this->shouldUsePriorityBooking() ? 'bookPrivileged' : 'book' }}">
              @csrf
              <div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {{-- Building --}}
                  <div class="md:col-span-2">
                    <label for="building_name" class="{{ $drawerLabelClass }}">Building</label>
                    <input wire:model="building_name" type="text" disabled name="building_name" class="{{ $drawerInputClass }} @error('building_name') is-invalid @enderror" value="{{old('building_name')}}">
                    @error('building_name')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Room --}}
                  <div>
                    <label for="room_name" class="{{ $drawerLabelClass }}">Room</label>
                    <input wire:model="room_name" type="text" name="room_name" disabled class="{{ $drawerInputClass }} @error('room_name') is-invalid @enderror" value="{{old('room_name')}}">
                    @error('room_name')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Date --}}
                  <div>
                      <label for="date" class="{{ $drawerLabelClass }}">Book Date</label>
                      <input required wire:model="book_date" type="date" disabled name="book_date" class="{{ $drawerInputClass }} @error('room_id') is-invalid @enderror" value="{{old('book_date')}}">
                    @error('book_date')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Time --}}
                  <div class="md:col-span-2">
                    {{-- Start Time --}}
                    <div class="inline-block mb-3 me-3 align-top w-full md:w-[31%]">
                      <label for="start_time_id" class="{{ $drawerLabelClass }}">Start Time</label>
                      <select required wire:model.live.debounce.500ms="start_time_id" name="start_time_id" class="{{ $drawerInputClass }} @error('start_time_id') is-invalid @enderror" value="{{old('start_time_id')}}">
                        @foreach ($timeSlots as $timeSlot)
                          @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00" && $timeSlot->id>=$this->initial_start_time_id && $timeSlot->id<=$this->initial_end_time_id)
                            <option value="{{ $timeSlot->id }}" class="text-black">
                              {{ $timeSlot->start_time }}
                            </option>
                          @endif
                        @endforeach
                      </select>
                    </div>

                    {{-- End Time --}}
                    <div class="inline-block mb-3 me-3 align-top w-full md:w-[31%]">
                      <label for="end_time_id" class="{{ $drawerLabelClass }}">End Time</label>
                      <select required wire:model.live.debounce.500ms="end_time_id" name="end_time_id" class="{{ $drawerInputClass }} @error('end_time_id') is-invalid @enderror" value="{{old('end_time_id')}}">
                        @foreach ( $timeSlots as $timeSlot )
                          @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00" && $timeSlot->id>=$this->initial_start_time_id && $timeSlot->id<=$this->initial_end_time_id)
                            <option value="{{ $timeSlot->id }}" class="text-black">
                              {{ $timeSlot->end_time }}
                            </option>
                          @endif
                        @endforeach
                      </select>
                    </div>

                    {{-- Vacancies Based on Time Slot --}}
                    <div class="inline-block mb-3 align-top w-full md:w-[31%]">
                      <label for="vacancies" class="{{ $drawerLabelClass }}">Vacancies</label>
                      <input required disabled wire:model="vacancies" name="vacancies" class="{{ $drawerInputClass }} @error('vacancies') is-invalid @enderror" >
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
                  <div>
                    <label for="number_occupants" class="{{ $drawerLabelClass }}">Number of Occupants</label>
                    <input required wire:model.live.debounce.500ms="number_occupants"
                     @if($this->shouldUsePriorityBooking()) disabled @endif
                     type="number" min="1" name="number_occupants" max="{{ (int) ($room_capacity ?? 1) }}" class="{{ $drawerInputClass }} @error('number_occupants') is-invalid @enderror" value="{{old('number_occupants')}}">
                    @error('number_occupants')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  {{-- Reason For Booking --}}
                  <div>
                    <label for="book_reason" class="{{ $drawerLabelClass }}">Reason For Booking</label>
                    <select required wire:model="book_reason" type="text" name="book_reason" class="{{ $drawerInputClass }} @error('book_reason') is-invalid @enderror" value="{{old('book_reason')}}">
                      <option class="text-black">--Select One--</option>
                      @if(auth()->user()->role->role_name=="Student")
                        <option value="Individual Study" class="text-black">Individual Study</option>
                        <option value="Group Study" class="text-black" @disabled((int) $this->number_occupants < 2)>Group Study</option>
                      @else
                        <option value="Individual Study" class="text-black">Individual Study</option>
                        <option value="Group Study" class="text-black">Group Study</option>
                        <option value="CAT" class="text-black">CAT</option>
                        <option value="Examination" class="text-black">Examination</option>
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
                      <div class="invalid-feedback md:col-span-2">
                        {{ $message }}
                      </div>
                  @enderror

                  @error('booking_duplicate')
                      <div class="invalid-feedback md:col-span-2">
                        {{ $message }}
                      </div>
                  @enderror

                  {{-- Edit if the error in number of occupants entered, number of vacancies etc --}}
                  @if (!$this->shouldUsePriorityBooking() && ((int) $this->vacancies <= 0 || (int) $this->vacancies < (int) $this->number_occupants))
                    <div class="invalid-feedback md:col-span-2">
                      The number of occupants exceeds the available slots.
                    </div>
                  @endif

                  </div>
                </div>
              <div class="mt-4 flex flex-wrap items-center gap-3">
                  <button type="button" wire:click="cancel" class="inline-flex items-center justify-center border border-white/20 hover:bg-white/5 text-gray-300 text-xs font-semibold py-2 px-4 rounded-lg cursor-pointer transition-colors">
                    <i class="bi bi-arrow-left"></i> Back
                  </button>
                  @if($this->shouldUsePriorityBooking())
                    <button type="submit" class="w-full bg-white text-[#02338D] font-bold font-sans py-3 rounded-lg shadow-md transition duration-150 mt-6 cursor-pointer hover:bg-gradient-to-r hover:from-[#0048AD] hover:to-[#FF383C] hover:text-white text-center">
                        <i class="bi-icons bi-bookmark-plus-fill"></i> Confirm High Priority Booking
                    </button>
                  @else
                    <button type="submit" class="w-full bg-white text-[#02338D] font-bold font-sans py-3 rounded-lg shadow-md transition duration-150 mt-6 cursor-pointer hover:bg-gradient-to-r hover:from-[#0048AD] hover:to-[#FF383C] hover:text-white text-center" {{ $this->vacancies <= 0 || $this->vacancies<$this->number_occupants ? 'disabled' : '' }}>
                        <i class="bi-icons bi-bookmark-plus-fill"></i> Confirm Booking
                    </button>
                  @endif
              </div>
          </form>
      </div>
    </div>
    @endif
</div>