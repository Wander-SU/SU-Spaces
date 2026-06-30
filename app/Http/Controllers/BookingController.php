<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\BaseBooking;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    private const UNDO_CANCEL_SECONDS = 30;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('quickAccess.view');
    }

    /**
     * Display previous bookings.
     */
    public function previousBookings(Request $request)
    {
        $userId = $request->user()->id;

        // Bound date filters to today to prevent future-date queries in the report view.
        $today = Carbon::today()->toDateString();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if (!empty($fromDate) && Carbon::parse($fromDate)->toDateString() > $today) {
            $fromDate = $today;
        }

        if (!empty($toDate) && Carbon::parse($toDate)->toDateString() > $today) {
            $toDate = $today;
        }

        if (!empty($fromDate) && !empty($toDate) && Carbon::parse($fromDate)->gt(Carbon::parse($toDate))) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $sortBy = $request->query('sort_by', 'newest');
        $sortDirection = $sortBy === 'oldest' ? 'asc' : 'desc';
        $hasAnyBookings = Booking::query()->where('user_id', $userId)->exists();

        $bookingsQuery = Booking::query()
            ->with(['room', 'startTimeSlot', 'endTimeSlot'])
            ->where('user_id', $userId)
            ->where('status', 'Booked');

        if (!empty($fromDate)) {
            $bookingsQuery->whereDate('booking_date', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $bookingsQuery->whereDate('booking_date', '<=', $toDate);
        }

        $bookings = $bookingsQuery
            ->orderBy('booking_date', $sortDirection)
            ->orderBy('start_time_id', $sortDirection)
            ->get()
            // Transform raw model data into the exact card payload expected by the Blade template.
            ->map(function (Booking $booking) {
                $bookingDate = Carbon::parse($booking->booking_date);
                $startTime = optional($booking->startTimeSlot)->start_time;
                $endTime = optional($booking->endTimeSlot)->end_time;

                $formattedStart = $startTime ? Carbon::parse($startTime)->format('g:ia') : 'TBA';
                $formattedEnd = $endTime ? Carbon::parse($endTime)->format('g:ia') : 'TBA';

                return [
                    'id' => $booking->id,
                    'room_name' => optional($booking->room)->room_name ?? 'ROOM',
                    'schedule' => $bookingDate->format('l, jS F Y') . ' | ' . $formattedStart . ' to ' . $formattedEnd,
                    'reason' => (string) ($booking->purpose ?? ''),
                    'occupants' => (int) ($booking->attendee_count ?? 0),
                    'status' => 'Confirmed',
                ];
            });

        $userRoleName = strtolower((string) optional($request->user()->role)->role_name);

        $priorityAlerts = collect();
        if ($userRoleName === 'student') {
            $priorityAlertsQuery = Booking::query()
                ->with('room')
                ->where('user_id', $userId)
                ->where('status', 'Voided')
                ->where(function ($query) {
                    $query->whereRaw('LOWER(purpose) LIKE ?', ['%cat%'])
                        ->orWhereRaw('LOWER(purpose) LIKE ?', ['%exam%'])
                        ->orWhereRaw('LOWER(purpose) LIKE ?', ['%examination%']);
                });

            if (!empty($fromDate)) {
                $priorityAlertsQuery->whereDate('booking_date', '>=', $fromDate);
            }

            if (!empty($toDate)) {
                $priorityAlertsQuery->whereDate('booking_date', '<=', $toDate);
            }

            $priorityAlerts = $priorityAlertsQuery
                ->latest('updated_at')
                ->take(5)
                ->get()
                // Map voided bookings into a simplified alert payload for the Priority Alerts section.
                ->map(function (Booking $booking) {
                    return [
                        'room_name' => optional($booking->room)->room_name ?? 'ROOM',
                        'status' => 'Reassigned',
                        'note' => 'Note: Room has been reassigned to Faculty for a CAT. Your booking has been cancelled.',
                    ];
                });
        }

        return view('allBookings.view', compact('bookings', 'priorityAlerts', 'fromDate', 'toDate', 'today', 'hasAnyBookings'));
    }

    /**
     * Cancel a booking from the previous bookings page.
     */
    public function cancelFromPrevious(Request $request, Booking $booking)
    {
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $roomName = optional($booking->room)->room_name ?? 'Selected room';
        $bookingDate = Carbon::parse($booking->booking_date)->format('jS F Y');

        if ($booking->status === 'Booked') {
            // Keep an auditable record using a valid enum status.
            Booking::query()
                ->whereKey($booking->id)
                ->update(['status' => 'Voided']);

            Cache::put($this->undoCacheKey($request->user()->id, $booking->id), true, now()->addSeconds(self::UNDO_CANCEL_SECONDS));
        }

        return redirect()
            // Keep active filter state after cancellation so the user stays in the same reporting context.
            ->route('bookings.previous', [
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'sort_by' => $request->input('sort_by', 'newest'),
            ])
            ->with('success', "Booking for {$roomName} on {$bookingDate} has been cancelled.")
            ->with('undo_booking_id', $booking->id)
            ->with('undo_expires_at', now()->addSeconds(self::UNDO_CANCEL_SECONDS)->timestamp);
    }

    /**
     * Undo a booking cancellation within a short server-verified window.
     */
    public function undoCancelFromPrevious(Request $request, Booking $booking)
    {
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $cacheKey = $this->undoCacheKey($request->user()->id, $booking->id);
        $undoAllowed = (bool) Cache::pull($cacheKey, false);

        if (!$undoAllowed || $booking->status !== 'Voided') {
            return redirect()
                ->route('bookings.previous', [
                    'from_date' => $request->input('from_date'),
                    'to_date' => $request->input('to_date'),
                    'sort_by' => $request->input('sort_by', 'newest'),
                ])
                ->with('error', 'Undo window has expired or this booking can no longer be restored.');
        }

        Booking::query()
            ->whereKey($booking->id)
            ->update(['status' => 'Booked']);

        return redirect()
            ->route('bookings.previous', [
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'sort_by' => $request->input('sort_by', 'newest'),
            ])
            ->with('success', 'Cancellation undone. Your booking has been restored.');
    }

    /**
     * Show edit page for switching building/room while keeping same date/time.
     */
    public function editFromPrevious(Request $request, Booking $booking)
    {
        $booking->load(['room.building', 'startTimeSlot', 'endTimeSlot']);

        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($booking->status !== 'Booked') {
            return redirect()->route('bookings.previous')->with('error', 'Only active bookings can be edited.');
        }

        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $sortBy = $request->query('sort_by', 'newest');

        $buildings = Building::query()
            ->orderBy('building_name', 'asc')
            ->get(['id', 'building_name', 'building_abbrev']);

        $defaultBuildingId = (int) (optional($booking->room)->building_id ?? 0);
        $selectedBuildingId = (int) $request->query('building_id', $defaultBuildingId);

        $availableRooms = $this->getAvailableRoomsForBookingSlot($booking, $selectedBuildingId > 0 ? $selectedBuildingId : null);

        return view('allBookings.edit', compact(
            'booking',
            'buildings',
            'selectedBuildingId',
            'availableRooms',
            'fromDate',
            'toDate',
            'sortBy'
        ));
    }

    /**
     * Persist updated room for a booking while preserving its date/time.
     */
    public function updateRoomFromPrevious(Request $request, Booking $booking)
    {
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($booking->status !== 'Booked') {
            return redirect()->route('bookings.previous')->with('error', 'Only active bookings can be edited.');
        }

        $validated = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'sort_by' => ['nullable', 'in:newest,oldest'],
        ]);

        $selectedBuildingId = (int) $validated['building_id'];
        $newRoomId = (int) $validated['room_id'];

        $availableRooms = $this->getAvailableRoomsForBookingSlot($booking, $selectedBuildingId);
        if (!$availableRooms->contains('id', $newRoomId)) {
            return back()
                ->withInput()
                ->with('error', 'Selected room is not available for that day and time.');
        }

        $booking->room_id = $newRoomId;
        $booking->save();

        return redirect()
            ->route('bookings.previous', [
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
                'sort_by' => $validated['sort_by'] ?? 'newest',
            ])
            ->with('success', 'Booking updated successfully.');
    }

    private function undoCacheKey(int $userId, int $bookingId): string
    {
        return 'bookings:undo:' . $userId . ':' . $bookingId;
    }

    private function getAvailableRoomsForBookingSlot(Booking $booking, ?int $buildingId = null): Collection
    {
        $bookingDate = Carbon::parse($booking->booking_date)->toDateString();
        $lessonDay = Carbon::parse($bookingDate)->format('l');
        $startTimeId = (int) $booking->start_time_id;
        $endTimeId = (int) $booking->end_time_id;

        $blockedByBaseBookingRoomIds = BaseBooking::query()
            ->where('lesson_day', $lessonDay)
            ->where('start_time_id', '<', $endTimeId)
            ->where('end_time_id', '>', $startTimeId)
            ->pluck('room_id')
            ->all();

        $blockedByBookingRoomIds = Booking::query()
            ->whereDate('booking_date', '=', $bookingDate, 'and')
            ->where('status', 'Booked')
            ->whereKeyNot($booking->id)
            ->where('start_time_id', '<', $endTimeId)
            ->where('end_time_id', '>', $startTimeId)
            ->pluck('room_id')
            ->all();

        $roomsQuery = Room::query()
            ->with('building')
            ->whereNotIn('id', array_unique(array_merge($blockedByBaseBookingRoomIds, $blockedByBookingRoomIds)))
            ->where('capacity', '>=', (int) $booking->attendee_count)
            ->orderBy('room_name', 'asc');

        if (!empty($buildingId)) {
            $roomsQuery->where('building_id', $buildingId);
        }

        return $roomsQuery->get();
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
    public function store(StoreBookingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
