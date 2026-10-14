<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\AvailabilityService as Svc;
use App\Support\RoomPresenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    /** GET /api/home?type= */
    public function index(Request $request, Svc $svc)
    {
        $request->validate([
            'type' => ['nullable', Rule::in(array_keys(config('spaces.types')))],
        ]);

        $now = now();
        $nowMin = $now->hour * 60 + $now->minute;

        $rooms = Room::with('building:id,building_name')->get();
        $sessions = $svc->sessionsByRoom($rooms->modelKeys(), date('l',strtotime($now->toDateString())));

        $freeNow = [];            // entries for rooms that are free this minute
        $countsByType = [];
        foreach ($rooms as $room) {
            [$free] = $svc->windows($sessions->get($room->id, collect()));
            [$status, $until, $left] = $svc->nowState($free, $nowMin);
            if ($status !== 'free') {
                continue;
            }
            $countsByType[$room->type] = ($countsByType[$room->type] ?? 0) + 1;
            $freeNow[] = compact('room', 'until', 'left');
        }

        $filtered = $request->filled('type')
            ? array_values(array_filter($freeNow, fn ($e) => $e['room']->type === $request->query('type')))
            : $freeNow;

        usort($filtered, fn ($a, $b) => $b['left'] <=> $a['left']); // longest free first

        $cards = array_map(fn ($e) => RoomPresenter::card($e['room'], [
            'status' => 'free',
            'free_until' => Svc::fmt($e['until']),
            'minutes_left' => $e['left'],
        ]), array_slice($filtered, 0, config('spaces.free_now_limit')));

        $types = [];
        foreach (config('spaces.types') as $key => $t) {
            $types[] = ['key' => $key, 'label' => $t['label'], 'count' => $countsByType[$key] ?? 0];
        }

        $user = auth('sanctum')->user();

        return response()->json([
            'meta' => [
                'date_label' => $now->format('l, M j'),
                'semester' => config('spaces.semester'),
                'free_count' => count($freeNow),
                'total_free_now' => count($filtered),
            ],
            'user' => $user ? ['name' => $user->name] : null,
            'types' => $types,
            'rooms' => $cards,
        ]);
    }
}
