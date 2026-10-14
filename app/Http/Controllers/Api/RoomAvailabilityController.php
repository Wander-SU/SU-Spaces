<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\AvailabilityService as Svc;
use App\Support\RoomPresenter;
use App\Support\UsageTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class RoomAvailabilityController extends Controller
{
    /** GET /api/rooms/availability?date=&from=&to=&type=&building_id=&min_seats=&sort= */
    public function index(Request $request, Svc $svc)
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'from' => ['required', 'date_format:H:i'],
            'to' => ['required', 'date_format:H:i', 'after:from'],
            'type' => ['nullable', Rule::in(array_keys(config('spaces.types')))],
            'building_id' => ['nullable', 'integer', 'exists:buildings,id'],
            'min_seats' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', Rule::in(['nearest', 'capacity', 'name'])],
        ]);
        $from = Svc::toMin($data['from']);
        $to = Svc::toMin($data['to']);

        // 1 query: candidate rooms (type is filtered later so the chip counts stay correct).
        $rooms = Room::with('building:id,building_name')
            ->when($data['building_id'] ?? null, fn ($q, $id) => $q->where('building_id', $id))
            ->when($data['min_seats'] ?? null, fn ($q, $n) => $q->where('capacity', '>=', $n))
            ->get();
        
        // Convert the date to a day of the week
        $data['date']=date('l',strtotime($data['date']));

        // 1 query: every session of those rooms on that date.
        $sessions = $svc->sessionsByRoom($rooms->modelKeys(), $data['date']);

        $fully = collect();
        $partly = collect();
        $countsByType = [];

        foreach ($rooms as $room) {
            // dd($room);
            [$free, $busy] = $svc->windows($sessions->get($room->id, collect()));
            // dd($free);
            $status = $svc->classify($free, $from, $to);
            if ($status === 'occupied') {
                continue;
            }

            $countsByType[$room->type] = ($countsByType[$room->type] ?? 0) + 1;

            if (!empty($data['type']) && $room->type !== $data['type']) {
                continue;
            }

            $entry = [
                'room' => $room,
                'card' => RoomPresenter::card($room, [
                    'status' => $status,
                    'free_windows' => Svc::present($free),
                    'busy_windows' => Svc::present($busy),
                ]),
            ];
            $status === 'free' ? $fully->push($entry) : $partly->push($entry);
        }

        $sort = $data['sort'] ?? 'nearest';
        $fully = $this->sorted($fully, $sort);
        $partly = $this->sorted($partly, $sort);

        $types = [];
        foreach (config('spaces.types') as $key => $t) {
            $count = $countsByType[$key] ?? 0;
            if ($count > 0 || ($data['type'] ?? null) === $key) {
                $types[] = ['key' => $key, 'label' => $t['label'], 'count' => $count];
            }
        }

        UsageTracker::searches(
            $fully->merge($partly)->map(fn ($e) => $e['room']->id)->all()
        );

        return response()->json([
            'meta' => [
                'day_start' => config('spaces.search_window.start'),
                'day_end' => config('spaces.search_window.end'),
            ],
            'types' => $types,
            'fully_free' => $fully->pluck('card')->values(),
            'partly_free' => $partly->pluck('card')->values(),
        ]);
    }

    /**
     * "nearest" has no user location to work with yet, so it groups by building and
     * floor. For true proximity add lat/lng to buildings, send the phone's position
     * with the request and order by distance here.
     */
    private function sorted(Collection $entries, string $sort): Collection
    {
        return (match ($sort) {
            'capacity' => $entries->sortByDesc(fn ($e) => $e['room']->capacity),
            'name' => $entries->sortBy(fn ($e) => $e['room']->name, SORT_NATURAL | SORT_FLAG_CASE),
            default => $entries->sortBy(fn ($e) => sprintf(
                '%s|%03d|%s', $e['room']->building->name, $e['room']->floor + 10, $e['room']->name
            )),
        })->values();
    }
}
