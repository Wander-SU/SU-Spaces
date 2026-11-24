<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * GET /api/admin/reports
     *   range:  ?days=30   or   ?from=2026-11-01&to=2026-11-20   (default: all time)
     *   scope:  ?building_id=3   ?type=computer_lab
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'from' => ['nullable', 'date_format:Y-m-d', 'required_with:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'required_with:from', 'after_or_equal:from'],
            'building_id' => ['nullable', 'integer', 'exists:buildings,id'],
            'type' => ['nullable', Rule::in(array_keys(config('spaces.types')))],
        ]);

        // Date range. Future days are never counted, so reports only show what has happened.
        $cap = now()->endOfDay();
        if (! empty($data['from'])) {
            $since = Carbon::createFromFormat('Y-m-d', $data['from'])->startOfDay();
            $until = Carbon::createFromFormat('Y-m-d', $data['to'])->endOfDay();
        } elseif (! empty($data['days'])) {
            $since = now()->subDays((int) $data['days'] - 1)->startOfDay();
            $until = $cap;
        } else {
            $since = null;
            $until = $cap;
        }
        if ($until->greaterThan($cap)) {
            $until = $cap;
        }

        $building = $data['building_id'] ?? null;
        $type = $data['type'] ?? null;

        // Building and room type apply to every query below ("r" is the rooms table).
        $scope = fn ($q) => $q
            ->when($building, fn ($q) => $q->where('r.building_id', $building))
            ->when($type, fn ($q) => $q->where('r.type', $type));

        $sel = fn () => $scope(DB::table('room_selections as s')
            ->join('rooms as r', 'r.id', '=', 's.room_id')
            ->join('buildings as b', 'b.id', '=', 'r.building_id')
            ->where('s.starts_at', '<=', $until)
            ->when($since, fn ($q) => $q->where('s.starts_at', '>=', $since)));

        $stats = fn () => $scope(DB::table('room_daily_stats as d')
            ->join('rooms as r', 'r.id', '=', 'd.room_id')
            ->join('buildings as b', 'b.id', '=', 'r.building_id')
            ->where('d.date', '<=', $until->toDateString())
            ->when($since, fn ($q) => $q->where('d.date', '>=', $since->toDateString())));

        $ratings = fn () => $scope(DB::table('room_feedback as f')
            ->join('room_selections as s', 's.id', '=', 'f.room_selection_id')
            ->join('rooms as r', 'r.id', '=', 's.room_id')
            ->where('f.created_at', '<=', $until)
            ->when($since, fn ($q) => $q->where('f.created_at', '>=', $since)));

        $totals = $sel()
            ->selectRaw('COUNT(*) as selections, COALESCE(SUM(s.participants), 0) as participants')
            ->first();

        $usage = $stats()
            ->selectRaw('COALESCE(SUM(d.clicks), 0) as clicks, COALESCE(SUM(d.searches), 0) as searches')
            ->first();

        $rating = $ratings()
            ->selectRaw('COUNT(*) as n, AVG(f.rating) as avg_rating, SUM(CASE WHEN f.has_issue THEN 1 ELSE 0 END) as issues')
            ->first();

        // With one building chosen, break the numbers down by room instead of by building.
        $breakdownBy = $building ? 'room' : 'building';
        $breakdown = ($building
            ? $sel()->selectRaw('r.id, r.room_name as name, COUNT(*) as selections, SUM(s.participants) as participants')
                ->groupBy('r.id', 'r.room_name')->limit(15)
            : $sel()->selectRaw('b.id, b.building_name as name, COUNT(*) as selections, SUM(s.participants) as participants')
                ->groupBy('b.id', 'b.building_name'))
            ->orderByRaw('SUM(s.participants) DESC')
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => $r->name,
                'selections' => (int) $r->selections,
                'participants' => (int) $r->participants,
            ]);

        $daily = $sel()
            ->selectRaw('DATE(s.starts_at) as day, COUNT(*) as selections, SUM(s.participants) as participants')
            ->groupByRaw('DATE(s.starts_at)')
            ->orderByRaw('DATE(s.starts_at)')
            ->get()
            ->map(fn ($r) => [
                'day' => (string) $r->day,
                'selections' => (int) $r->selections,
                'participants' => (int) $r->participants,
            ]);

        $people = $sel()
            ->selectRaw('r.id, COUNT(*) as selections, SUM(s.participants) as participants')
            ->groupBy('r.id')
            ->get()
            ->keyBy('id');

        $topRooms = $stats()
            ->selectRaw('r.id, r.room_name as name, b.building_name as building, SUM(d.clicks) as clicks, SUM(d.searches) as searches')
            ->groupBy('r.id', 'r.room_name', 'b.building_name')
            ->havingRaw('SUM(d.clicks) > 0')
            ->orderByRaw('SUM(d.clicks) DESC')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => $r->name,
                'building' => $r->building,
                'clicks' => (int) $r->clicks,
                'searches' => (int) $r->searches,
                'selections' => (int) ($people[$r->id]->selections ?? 0),
                'participants' => (int) ($people[$r->id]->participants ?? 0),
            ]);

        return response()->json([
            'range' => [
                'from' => $since?->toDateString(),
                'to' => $until->toDateString(),
            ],
            'totals' => [
                'selections' => (int) $totals->selections,
                'participants' => (int) $totals->participants,
                'clicks' => (int) $usage->clicks,
                'searches' => (int) $usage->searches,
                'feedback_count' => (int) $rating->n,
                'avg_rating' => $rating->n ? round((float) $rating->avg_rating, 1) : null,
                'issues' => (int) $rating->issues,
            ],
            'breakdown_by' => $breakdownBy,
            'by_building' => $breakdown->values(),
            'daily' => $daily->values(),
            'top_rooms' => $topRooms->values(),
            // Always the full lists, so the app can fill its dropdowns whatever is selected.
            'filters' => [
                'buildings' => Building::orderBy('building_name')->get(['id', 'building_name'])
                    ->map(fn ($b) => ['id' => $b->id, 'name' => $b->building_name])->values(),
                'types' => collect(config('spaces.types'))
                    ->map(fn ($t, $k) => ['key' => $k, 'label' => $t['label']])->values(),
            ],
        ]);
    }
}