<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\RoomIssueReported;
use App\Models\Room;
use App\Models\RoomSelection;
use App\Services\AvailabilityService as Svc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        return response()->json($this->summary($room, $startsAt, $endsAt, auth('sanctum')->user()?->id, $data['participants'] ?? null));
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
                ...$this->summary($room, $startsAt, $endsAt, $user->id, $data['participants']),
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

        return response()->json($this->summary($room, $startsAt, $endsAt, $user->id, $data['participants']), 201);
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
    private function summary(Room $room, Carbon $startsAt, Carbon $endsAt, ?int $userId, ?int $wanted = null): array
    {
        $q = RoomSelection::where('room_id', $room->id)->overlapping($startsAt, $endsAt);
        $mine = $userId ? (clone $q)->where('user_id', $userId)->first() : null;

        $taken = (int) (clone $q)->sum('participants');
        $left = max(0, $room->capacity - $taken);

        return [
            'groups' => (clone $q)->distinct()->count('user_id'),
            'participants' => $taken,
            'capacity' => $room->capacity,
            'seats_left' => $left,
            'enough_for_you' => $wanted === null ? null : $left >= $wanted,
            'mine' => $mine ? [
                'id' => $mine->id,
                'starts_at' => $mine->starts_at->format('H:i'),
                'ends_at' => $mine->ends_at->format('H:i'),
            ] : null,
        ];
    }

    /** GET /api/me/selections?status=active|past */
    public function mine(Request $request)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'past'])]]);

        $q = RoomSelection::with(['room' => fn ($r) => $r->withTrashed()->with('building:id,building_name')])
            ->withExists('feedback')
            ->where('user_id', $request->user()->id);

        $q = $data['status'] === 'active'
            ? $q->active()->orderBy('starts_at')
            : $q->where('ends_at', '<=', now())->orderByDesc('starts_at');

        return response()->json(['data' => $q->limit(50)->get()->map(fn ($s) => $this->present($s))->values()]);
    }

    /** POST /api/selections/{selection}/feedback  {rating, has_issue, comment} */
    public function feedback(Request $request, RoomSelection $selection)
    {
        abort_unless($selection->user_id === $request->user()->id, 403);

        if ($selection->ends_at->isFuture()) {
            return response()->json(['message' => 'You can rate a room once your slot has ended.'], 422);
        }
        if ($selection->feedback()->exists()) {
            return response()->json(['message' => 'You have already left feedback for this selection.'], 409);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'has_issue' => ['sometimes', 'boolean'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // 1 or 2 stars always counts as a problem.
        $hasIssue = (bool) ($data['has_issue'] ?? false) || $data['rating'] <= 2;
        $comment = trim($data['comment'] ?? '');

        if ($hasIssue && mb_strlen($comment) < 5) {
            throw ValidationException::withMessages([
                'comment' => 'Please tell us what was wrong (a few words is enough).',
            ]);
        }

        $feedback = $selection->feedback()->create([
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'has_issue' => $hasIssue,
            'comment' => $comment !== '' ? $comment : null,
        ]);

        // A problem was reported: tell maintenance.
        $to = config('spaces.maintenance.email');
        if ($to && $feedback->has_issue) {
            try {
                Mail::to($to)->send(new RoomIssueReported($feedback));
            } catch (\Throwable $e) {
                report($e); // a mail problem must never lose the student's feedback
            }
        }

        return response()->json(['saved' => true], 201);
    }

    /** GET /api/me/feedback-prompts: ended slots flagged by the scheduler, still waiting for feedback */
    public function prompts(Request $request)
    {
        $items = RoomSelection::with(['room' => fn ($r) => $r->withTrashed()->with('building:id,building_name')])
            ->where('user_id', $request->user()->id)
            ->whereNotNull('feedback_prompted_at')
            ->where('ends_at', '>=', now()->subDay())   // don't nag about old slots
            ->whereDoesntHave('feedback')
            ->orderBy('ends_at')
            ->get();

        return response()->json(['data' => $items->map(fn ($s) => $this->present($s))->values()]);
    }

    private function present(RoomSelection $s): array
    {
        return [
            'id' => $s->id,
            'room_id' => $s->room_id,
            'room_name' => $s->room?->room_name ?? 'Removed room',
            'building' => $s->room?->building?->building_name ?? '',
            'date' => $s->starts_at->format('Y-m-d'),
            'start' => $s->starts_at->format('H:i'),
            'end' => $s->ends_at->format('H:i'),
            'participants' => $s->participants,
            'has_feedback' => (bool) ($s->feedback_exists ?? false),
        ];
    }
}