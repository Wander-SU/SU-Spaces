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

        // One aggregate query for every building.
        $stats = DB::table('room_daily_stats as s')
            ->join('rooms as r', 'r.id', '=', 's.room_id')
            ->when($request->filled('days'), fn ($q) => $q->where('s.date', '>=', now()->subDays((int) $request->query('days'))->toDateString()))
            ->selectRaw('r.building_id, SUM(s.clicks) as clicks, SUM(s.searches) as searches')
            ->groupBy('r.building_id')
            ->get()
            ->keyBy('building_id');

        $phases = Phase::with(['buildings' => fn ($q) => $q->withCount('rooms')->orderBy('building_name')])
            ->orderBy('id')
            ->get();

        return response()->json([
            'phases' => $phases->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->phase_name,
                'buildings' => $p->buildings->map(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->building_name,
                    'clicks' => (int) ($stats[$b->id]->clicks ?? 0),
                    'searches' => (int) ($stats[$b->id]->searches ?? 0),
                    'room_count' => $b->rooms_count,
                ])->values(),
            ])->values(),
        ]);
    }
}
