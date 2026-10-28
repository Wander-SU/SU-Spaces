<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\AvailabilityService as Svc;
use App\Support\RoomPresenter;
use App\Support\UsageTracker;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /** GET /api/rooms/{room}  (counts as a "click") */
    public function show(Room $room, Svc $svc)
    {
        $room->load('building:id,building_name');
        UsageTracker::click($room);

        $now = now();
        $nowMin = $now->hour * 60 + $now->minute;
        $sessions = $svc->sessionsByRoom([$room->id], date('l',strtotime($now->toDateString())))->get($room->id, collect());
        [$free] = $svc->windows($sessions);
        [$status, $until, $left] = $svc->nowState($free, $nowMin);
        

        // dd($sessions);
        $next = $sessions->first(fn ($s) => Svc::toMin($s->startTimeSlot->start_time) > $nowMin);
        $current = $sessions->first(
            fn ($s) => Svc::toMin($s->startTimeSlot->start_time) <= $nowMin && $nowMin < Svc::toMin($s->endTimeSlot->end_time)
        );

        // dd($current);

        if ($nowMin < $svc->openMin()) {
            $message = 'Closed. Opens at ' . Svc::fmt12($svc->openMin()) . '.';
        } elseif ($nowMin >= $svc->closeMin()) {
            $message = 'Closed for today.';
        } elseif ($status === 'free') {
            $message = 'Open for walk-in study until ' . Svc::fmt12($until) . '.';
            $message .= $next
                ? ' Next scheduled session: ' . $next->title . ' at ' . Svc::fmt12(Svc::toMin($next->startTimeSlot->start_time)) . '.'
                : ' No more sessions today.';
        } else {
            $message = $current
                ? 'In use: ' . trim($current->title . ' ' . ($current->unit_name ?? ''))
                  . ' until ' . Svc::fmt12(Svc::toMin($current->endTimeSlot->end_time)) . '.'
                : 'Currently in use.';
        }

        return response()->json([
            'room' => RoomPresenter::card($room, [
                'status' => $status,
                'free_until' => $until !== null ? Svc::fmt($until) : null,
                'minutes_left' => $left,
            ]),
            'status_message' => $message,
            'schematic_svg' => $room->schematic_svg,
            'location' => [
                'block' => $room->location_block ?: $room->building->name,
                'level' => $room->level_label,
                'hint' => $room->location_hint,
                'svg' => $room->location_svg,
            ],
        ]);
    }

    /** GET /api/rooms/{room}/schedule?date=yyyy-mm-dd */
    public function schedule(Request $request, Room $room, Svc $svc)
    {
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        $sessions = $svc->sessionsByRoom([$room->id], date('l',strtotime($data['date'])))->get($room->id, collect());

        return response()->json([
            'day_start' => Svc::fmt($svc->openMin()),
            'day_end' => Svc::fmt($svc->closeMin()),
            'current_time' => $data['date'] === now()->toDateString() ? now()->format('H:i') : null,
            'slots' => $svc->daySlots($sessions),
        ]);
    }
}
