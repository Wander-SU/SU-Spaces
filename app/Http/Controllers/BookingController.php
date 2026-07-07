<?php

namespace App\Http\Controllers;

use App\Mail\BookingVoided;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\BaseBooking;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

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
        $editBookingId = (int) $request->query('edit_booking', 0);
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

        $priorityAlertsQuery = Booking::query()
            ->with('room')
            ->where('user_id', $userId)
            ->where('status', 'Voided');

        if (!empty($fromDate)) {
            $priorityAlertsQuery->whereDate('booking_date', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $priorityAlertsQuery->whereDate('booking_date', '<=', $toDate);
        }

        $voidedBookings = $priorityAlertsQuery
            ->latest('updated_at')
            ->take(20)
            ->get();

        $priorityAlerts = collect();
        if ($voidedBookings->isNotEmpty()) {
            $roomIds = $voidedBookings->pluck('room_id')->filter()->unique()->values();
            $bookingDates = $voidedBookings->pluck('booking_date')->filter()->unique()->values();

            $overridingCandidates = Booking::query()
                ->with(['user.role'])
                ->where('status', 'Booked')
                ->where('user_id', '!=', $userId)
                ->whereIn('room_id', $roomIds)
                ->whereIn('booking_date', $bookingDates)
                ->get();

            $priorityAlerts = $voidedBookings
                ->map(function (Booking $voidedBooking) use ($overridingCandidates) {
                    $override = $overridingCandidates->first(function (Booking $candidate) use ($voidedBooking) {
                        if ((int) $candidate->room_id !== (int) $voidedBooking->room_id) {
                            return false;
                        }

                        if ((string) $candidate->booking_date !== (string) $voidedBooking->booking_date) {
                            return false;
                        }

                        $roleName = strtolower((string) optional(optional($candidate->user)->role)->role_name);
                        if ($roleName === 'student' || $roleName === '') {
                            return false;
                        }

                        return (int) $candidate->start_time_id <= (int) $voidedBooking->end_time_id
                            && (int) $candidate->end_time_id >= (int) $voidedBooking->start_time_id;
                    });

                    if (!$override) {
                        return null;
                    }

                    $faculty = trim((string) optional($override->user)->faculty);
                    $facultyLabel = $faculty !== '' ? $faculty : 'Not specified';

                    return [
                        'room_name' => optional($voidedBooking->room)->room_name ?? 'ROOM',
                        'status' => 'Reassigned',
                        'note' => 'Note: Your booking was overridden by a faculty priority booking.',
                        'faculty' => $facultyLabel,
                    ];
                })
                ->filter()
                ->take(5)
                ->values();
        }

        // Optional right-drawer edit context rendered on the previous bookings page.
        $editBooking = null;
        $buildings = collect();
        $selectedBuildingId = null;
        $availableRooms = collect();

        if ($editBookingId > 0) {
            $editBooking = Booking::query()
                ->with(['room.building', 'startTimeSlot', 'endTimeSlot'])
                ->where('user_id', $userId)
                ->where('status', 'Booked')
                ->find($editBookingId);

            if ($editBooking) {
                $buildings = Building::query()
                    ->orderBy('building_name', 'asc')
                    ->get(['id', 'building_name', 'building_abbrev']);

                $defaultBuildingId = (int) (optional($editBooking->room)->building_id ?? 0);
                $selectedBuildingId = (int) $request->query('building_id', $defaultBuildingId);
                $editorRoleName = strtolower((string) optional(optional($request->user())->role)->role_name);
                $allowPriorityOverrideSelection = $editorRoleName !== 'student';
                $availableRooms = $this->getAvailableRoomsForBookingSlot(
                    $editBooking,
                    $selectedBuildingId > 0 ? $selectedBuildingId : null,
                    $allowPriorityOverrideSelection
                );
            }
        }

        return view('allBookings.view', compact(
            'bookings',
            'priorityAlerts',
            'fromDate',
            'toDate',
            'today',
            'hasAnyBookings',
            'sortBy',
            'editBooking',
            'buildings',
            'selectedBuildingId',
            'availableRooms'
        ));
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

        return redirect()->route('bookings.previous', [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'sort_by' => $request->query('sort_by', 'newest'),
            'edit_booking' => $booking->id,
            'building_id' => $request->query('building_id', (int) (optional($booking->room)->building_id ?? 0)),
        ]);
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
            'purpose' => ['nullable', 'string', 'max:255'],
            'attendee_count' => ['nullable', 'integer', 'min:1'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'sort_by' => ['nullable', 'in:newest,oldest'],
        ]);

        $selectedBuildingId = (int) $validated['building_id'];
        $newRoomId = (int) $validated['room_id'];
        $roleName = strtolower((string) optional(optional($request->user())->role)->role_name);
        $isStudent = $roleName === 'student';

        $availableRooms = $this->getAvailableRoomsForBookingSlot($booking, $selectedBuildingId, !$isStudent);
        if (!$availableRooms->contains('id', $newRoomId)) {
            return back()
                ->withInput()
                ->with('error', 'Selected room is not available for that day and time.');
        }

        $selectedRoom = $availableRooms->firstWhere('id', $newRoomId);
        $roomCapacity = (int) (optional($selectedRoom)->capacity ?? 0);
        $requestedPurpose = trim((string) ($validated['purpose'] ?? (string) $booking->purpose));
        $isHighPriorityReason = in_array(strtolower($requestedPurpose), ['cat', 'examination', 'exam'], true);

        $bookingDate = Carbon::parse($booking->booking_date)->toDateString();
        $startTimeId = (int) $booking->start_time_id;
        $endTimeId = (int) $booking->end_time_id;

        $hasBaseTimeConflict = BaseBooking::query()
            ->where('room_id', $newRoomId)
            ->where('lesson_day', Carbon::parse($bookingDate)->format('l'))
            ->where('start_time_id', '<', $endTimeId)
            ->where('end_time_id', '>', $startTimeId)
            ->exists();

        if ($hasBaseTimeConflict) {
            return back()
                ->withInput()
                ->with('error', 'Selected room has a base timetable conflict for that time.');
        }

        $overlappingBookings = Booking::query()
            ->with(['user.role', 'startTimeSlot', 'endTimeSlot'])
            ->whereDate('booking_date', $bookingDate)
            ->where('status', 'Booked')
            ->where('room_id', $newRoomId)
            ->whereKeyNot($booking->id)
            ->where('start_time_id', '<', $endTimeId)
            ->where('end_time_id', '>', $startTimeId)
            ->get();

        if (!$isStudent) {
            if (!in_array($requestedPurpose, ['Individual Study', 'Group Study', 'CAT', 'Examination'], true)) {
                return back()
                    ->withInput()
                    ->withErrors(['purpose' => 'Please choose a valid booking reason.']);
            }

            if (!$isHighPriorityReason && $overlappingBookings->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('error', 'Selected room is not available for that day and time.');
            }

            if ($isHighPriorityReason && $overlappingBookings->isNotEmpty()) {
                foreach ($overlappingBookings as $conflictingBooking) {
                    if (!$this->canOverrideByRole($request->user(), $conflictingBooking->user, $conflictingBooking)) {
                        $blockedByRole = (string) optional(optional($conflictingBooking->user)->role)->role_name;
                        $blockedByName = (string) optional($conflictingBooking->user)->name;
                        return back()
                            ->withInput()
                            ->with('error', 'Cannot apply high priority override. '.$blockedByName.' ('.$blockedByRole.') has a protected booking in that slot.');
                    }
                }

                foreach ($overlappingBookings as $conflictingBooking) {
                    $conflictingBooking->status = 'Voided';
                    $conflictingBooking->save();
                    $recipientEmail = (string) optional($conflictingBooking->user)->email;
                    if ($recipientEmail !== '') {
                        try {
                            Mail::to($recipientEmail)->send(new BookingVoided($conflictingBooking, $requestedPurpose));
                        } catch (\Throwable $mailException) {
                            report($mailException);
                        }
                    }
                }
            }

            $updatedAttendeeCount = (int) ($validated['attendee_count'] ?? 1);
            if ($isHighPriorityReason) {
                $updatedAttendeeCount = max(1, $roomCapacity);
            } else {
                $updatedAttendeeCount = max(1, min($updatedAttendeeCount, max(1, $roomCapacity)));
            }

            $booking->purpose = $requestedPurpose;
            $booking->attendee_count = $updatedAttendeeCount;
        } elseif ($overlappingBookings->isNotEmpty()) {
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

    private function getAvailableRoomsForBookingSlot(Booking $booking, ?int $buildingId = null, bool $includeBookedForPriorityEditors = false): Collection
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

        $blockedRoomIds = $includeBookedForPriorityEditors
            ? $blockedByBaseBookingRoomIds
            : array_unique(array_merge($blockedByBaseBookingRoomIds, $blockedByBookingRoomIds));

        $roomsQuery = Room::query()
            ->with('building')
            ->whereNotIn('id', $blockedRoomIds)
            ->orderBy('room_name', 'asc');

        if (!empty($buildingId)) {
            $roomsQuery->where('building_id', $buildingId);
        }

        return $roomsQuery->get();
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

    private function canOverrideByRole(User $actor, User $target, Booking $targetBooking): bool
    {
        $actorRole = $this->normalizeOverrideRole((string) optional($actor->role)->role_name);
        $targetRole = $this->normalizeOverrideRole((string) optional($target->role)->role_name);
        $targetIsHighPriority = $this->isHighPriorityPurpose((string) $targetBooking->purpose);

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
