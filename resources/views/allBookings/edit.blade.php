@extends('layouts.app')

@section('title', 'Edit Booking')
@section('page-title', 'Edit Booking')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bookings.previous') }}">Previous Bookings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Booking</li>
@endsection

@section('content')
    <div class="rounded-xl border border-[#e3e3e0] bg-white p-4 md:p-6 dark:border-[#3E3E3A] dark:bg-[#161615]">
        <h2 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Change Building and Room</h2>

        <div class="mb-5 rounded-lg border border-[#e3e3e0] p-4 text-sm text-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#EDEDEC]">
            <p><strong>Current room:</strong> {{ optional($booking->room)->room_name ?? 'Room not available' }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('jS F Y') }}</p>
            <p><strong>Time:</strong> {{ optional($booking->startTimeSlot)->start_time ?? 'TBA' }} - {{ optional($booking->endTimeSlot)->end_time ?? 'TBA' }}</p>
            <p><strong>Occupants:</strong> {{ (int) $booking->attendee_count }}</p>
            <p><strong>Reason:</strong> {{ (string) $booking->purpose }}</p>
        </div>

        <form method="GET" action="{{ route('bookings.previous.edit', $booking->id) }}" class="mb-4">
            <input type="hidden" name="from_date" value="{{ $fromDate }}">
            <input type="hidden" name="to_date" value="{{ $toDate }}">
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">

            <label for="building_id" class="mb-1 block text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Building</label>
            <select id="building_id" name="building_id" class="w-full md:w-80 border border-[#e3e3e0] rounded-md px-2 py-2 text-sm text-[#1b1b18] bg-white dark:border-[#3E3E3A] dark:bg-white dark:text-[#1b1b18]" onchange="this.form.submit()">
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}" @selected((int) $selectedBuildingId === (int) $building->id)>
                        {{ $building->building_name }}{{ !empty($building->building_abbrev) ? ' (' . $building->building_abbrev . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        <form method="POST" action="{{ route('bookings.previous.update-room', $booking->id) }}">
            @csrf
            <input type="hidden" name="from_date" value="{{ $fromDate }}">
            <input type="hidden" name="to_date" value="{{ $toDate }}">
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
            <input type="hidden" name="building_id" value="{{ $selectedBuildingId }}">

            <label for="room_id" class="mb-1 block text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Available rooms for same day and time</label>
            <select id="room_id" name="room_id" class="w-full md:w-96 border border-[#e3e3e0] rounded-md px-2 py-2 text-sm text-[#1b1b18] bg-white dark:border-[#3E3E3A] dark:bg-white dark:text-[#1b1b18]" @disabled($availableRooms->isEmpty())>
                @forelse($availableRooms as $room)
                    <option value="{{ $room->id }}" @selected((int) $room->id === (int) $booking->room_id)>
                        {{ $room->room_name }} (Capacity: {{ (int) $room->capacity }})
                    </option>
                @empty
                    <option value="">No available rooms found for this time slot</option>
                @endforelse
            </select>
            @error('room_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex items-center gap-3">
                <a href="{{ route('bookings.previous', ['from_date' => $fromDate, 'to_date' => $toDate, 'sort_by' => $sortBy]) }}" class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Back</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-[#0048AD] px-4 py-2 text-sm font-medium text-white hover:brightness-95" @disabled($availableRooms->isEmpty())>Save changes</button>
            </div>
        </form>
    </div>
@endsection
