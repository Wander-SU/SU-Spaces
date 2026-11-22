<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    /** GET /api/admin/overview?days=30   (days optional; default = all time) */
    public function index(Request $request)
    {
        $request->validate(['days' => ['nullable', 'integer', 'min:1', 'max:3650']]);
        $since = $request->filled('days') ? now()->subDays((int) $request->query('days')) : null;

        // Clicks and searches per room.
        $roomStats = DB::table('room_daily_stats')
            ->when($since, fn ($q) => $q->where('date', '>=', $since->toDateString()))
            ->selectRaw('room_id, SUM(clicks) as clicks, SUM(searches) as searches')
            ->groupBy('room_id')
            ->get()
            ->keyBy('room_id');

        // Selections per room.
        $selections = DB::table('room_selections')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->selectRaw('room_id, COUNT(*) as total')
            ->groupBy('room_id')
            ->pluck('total', 'room_id');

        $phases = Phase::with(['buildings' => fn ($q) => $q
            ->withCount('rooms')
            ->with('rooms')
            ->orderBy('building_name')])
            ->orderBy('id')
            ->get();

        return response()->json([
            'phases' => $phases->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->phase_name,
                'buildings' => $p->buildings->map(function ($b) use ($roomStats, $selections) {
                    $rooms = $b->rooms->map(fn ($r) => [
                        'id' => $r->id,
                        'name' => $r->room_name,
                        'code' => $r->code ?? '',
                        'floor' => $r->floor_label,
                        'area' => $r->area ?? '',
                        'capacity' => $r->capacity,
                        'type' => $r->type,
                        'clicks' => (int) ($roomStats[$r->id]->clicks ?? 0),
                        'searches' => (int) ($roomStats[$r->id]->searches ?? 0),
                        'selections' => (int) ($selections[$r->id] ?? 0),
                    ])->sortBy([['clicks', 'desc'], ['name', 'asc']])->values();

                    return [
                        'id' => $b->id,
                        'name' => $b->building_name,
                        'clicks' => $rooms->sum('clicks'),
                        'searches' => $rooms->sum('searches'),
                        'selections' => $rooms->sum('selections'),
                        'room_count' => $b->rooms_count,
                        'rooms' => $rooms,
                    ];
                })->values(),
            ])->values(),
        ]);
    }
}
