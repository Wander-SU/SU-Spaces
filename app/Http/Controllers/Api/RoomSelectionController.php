<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomSelection;
use App\Services\AvailabilityService as Svc;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomSelectionController extends Controller
{
    /** GET /api/rooms/{room}/selections?date&from&to  (public; adds "mine" when signed in) */
    public function index(Request $request, Room $room)
    {
        $data = $this->validated($request, false);
        if ($data['to'] <= $data['from']) {
            return response()->json(['message' => 'The end time must be after the start time.'], 422);
        }
        [$startsAt, $endsAt] = $this->period($data);

        return response()->json($this->summary($room, $startsAt, $endsAt, auth('sanctum')->user()?->id));
    }

    /** POST /api/rooms/{room}/select  {date, from, to, participants}  (signed in) */
    public function store(Request $request, Room $room, Svc $svc)
    {
        $data = $this->validated($request, true);
        if ($data['to'] <= $data['from']) {
            return response()->json(['message' => 'The end time must be after the start time.'], 422);
        }
        [$startsAt, $endsAt] = $this->period($data);
        $user = $request->user();

        if ($endsAt->isPast()) {
            return response()->json(['message' => 'That time slot has already ended.'], 422);
        }

        // The room must be free of timetabled classes for the whole slot.
        $sessions = $svc->sessionsByRoom([$room->id], $startsAt->format('l'))->get($room->id, collect());
        [$free] = $svc->windows($sessions);
        if ($svc->classify($free, Svc::toMin($data['from']), Svc::toMin($data['to'])) !== 'free') {
            return response()->json(['message' => 'This room is not free for the whole of that time.'], 422);
        }

        // One selection per person, per room, per period.
        $mine = RoomSelection::where('user_id', $user->id)
            ->where('room_id', $room->id)
            ->overlapping($startsAt, $endsAt)
            ->first();

        if ($mine) {
            return response()->json([
                'message' => 'You have already selected this room from '
                    . $mine->starts_at->format('g:i A') . ' to ' . $mine->ends_at->format('g:i A') . '.',
                ...$this->summary($room, $startsAt, $endsAt, $user->id),
            ], 409);
        }

        // At most N new selections per rolling window.
        $max = config('spaces.selection.max_per_window');
        $window = config('spaces.selection.window_minutes');
        $recent = RoomSelection::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes($window))
            ->orderBy('created_at')
            ->get();

        if ($recent->count() >= $max) {
            $retryAt = $recent->first()->created_at->copy()->addMinutes($window);

            return response()->json([
                'message' => "You can select up to {$max} rooms per hour. Try again at {$retryAt->format('g:i A')}.",
                'retry_at' => $retryAt->toIso8601String(),
            ], 429);
        }

        RoomSelection::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'participants' => $data['participants'],
        ]);

        return response()->json($this->summary($room, $startsAt, $endsAt, $user->id), 201);
    }

    private function validated(Request $request, bool $needsParticipants): array
    {
        return $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'from' => ['required', 'date_format:H:i'],
            'to' => ['required', 'date_format:H:i'],
            'participants' => [$needsParticipants ? 'required' : 'nullable', 'integer', 'min:1', 'max:5000'],
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function period(array $data): array
    {
        return [
            Carbon::createFromFormat('Y-m-d H:i', "{$data['date']} {$data['from']}"),
            Carbon::createFromFormat('Y-m-d H:i', "{$data['date']} {$data['to']}"),
        ];
    }

    /** Groups and people for the period, plus the signed-in user's own selection (if any). */
    private function summary(Room $room, Carbon $startsAt, Carbon $endsAt, ?int $userId): array
    {
        $q = RoomSelection::where('room_id', $room->id)->overlapping($startsAt, $endsAt);
        $mine = $userId ? (clone $q)->where('user_id', $userId)->first() : null;

        return [
            'groups' => (clone $q)->distinct()->count('user_id'),
            'participants' => (int) (clone $q)->sum('participants'),
            'mine' => $mine ? [
                'id' => $mine->id,
                'starts_at' => $mine->starts_at->format('H:i'),
                'ends_at' => $mine->ends_at->format('H:i'),
            ] : null,
        ];
    }
}