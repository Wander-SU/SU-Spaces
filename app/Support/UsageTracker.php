<?php

namespace App\Support;

use App\Models\Room;
use Illuminate\Support\Facades\DB;

/**
 * Counts clicks (room opened) and searches (room appeared in results) per room per day.
 * Runs after the response is sent and never breaks a request if it fails.
 */
class UsageTracker
{
    public static function click(Room $room): void
    {
        self::bump([$room->id], 'clicks');
    }

    /** @param int[] $roomIds */
    public static function searches(array $roomIds): void
    {
        self::bump($roomIds, 'searches');
    }

    private static function bump(array $ids, string $column): void
    {
        $ids = array_values(array_unique($ids));
        if (!$ids) {
            return;
        }

        app()->terminating(function () use ($ids, $column) {
            try {
                $today = now()->toDateString();
                foreach (array_chunk($ids, 500) as $chunk) {
                    DB::table('room_daily_stats')->insertOrIgnore(array_map(
                        fn ($id) => ['room_id' => $id, 'date' => $today, 'clicks' => 0, 'searches' => 0],
                        $chunk
                    ));
                    DB::table('room_daily_stats')
                        ->where('date', $today)
                        ->whereIn('room_id', $chunk)
                        ->increment($column);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
